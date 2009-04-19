//
//	guiedit - PukiWiki Plugin
//
//	License:
//	  GNU General Public License Version 2 or later (GPL)
//	  http://www.gnu.org/licenses/gpl.html
//
//	Copyright (C) 2006-2007 garand
//	PukiWiki : Copyright (C) 2001-2006 PukiWiki Developers Team
//	FCKeditor : Copyright (C) 2003-2007 Frederico Caldeira Knabben
//


///////////////////////////////////////////////////////////
//	コマンド

//	テーブル プロパティ
FCKCommands.GetCommand('Table').Url = FCKPlugins.Items['TableEx'].Path + 'Table.html';
FCKCommands.GetCommand('Table').Width = 260;
FCKCommands.GetCommand('Table').Height = 160;

FCKCommands.GetCommand('Table').GetState = function() {
	var tags = new Array('H2', 'H3', 'H4', 'PRE', 'OL', 'UL', 'DL');
	for (i = 0; i < tags.length; i++) {
		if (FCKSelection.HasAncestorNode(tags[i])) {
			return FCK_TRISTATE_DISABLED;
		}
	}
	
	var oElement = FCKSelection.GetSelectedElement();
	if (oElement && oElement.tagName.Equals('IMG', 'HR', 'DIV', 'SPAN')) {
		return FCK_TRISTATE_DISABLED;
	}
	
	return FCK_TRISTATE_OFF;
}


//	セル プロパティ
FCKCommands.RegisterCommand('TableCellProp',
	new FCKDialogCommand('TableCellEx',  FCKLang.CellProperties,
						 FCKPlugins.Items['TableEx'].Path + 'TableCell.html', 380, 200
	)
);


//	列 プロパティ
FCKCommands.RegisterCommand('TableCol',
	new FCKDialogCommand('TableCol',  FCKLang.TableColDlgTitle,
						 FCKPlugins.Items['TableEx'].Path + 'TableCol.html', 380, 180
	)
);


//	セルの挿入

FCKCommands.GetCommand('TableInsertCellBefore').Execute = function() {
	TableInsertCell(true);
}

FCKCommands.GetCommand('TableInsertCellAfter').Execute = function() {
	TableInsertCell(false);
}

function TableInsertCell(insertBefore) {
	FCKUndo.SaveUndoStep();
	
	var oCell = FCKSelection.MoveToAncestorNode('TD') || FCKSelection.MoveToAncestorNode('TH');
	
	oCell = FCKTableHandler.InsertCell(oCell, insertBefore);
	
	oCell.className = 'style_td';
	
	FCKUndo.SaveUndoStep();
}


//	列挿入

FCKCommands.GetCommand('TableInsertColumnBefore').Execute = function() {
	TableInsertCol(true);
}

FCKCommands.GetCommand('TableInsertColumnAfter').Execute = function() {
	TableInsertCol(false);
}

function TableInsertCol(insertBefore) {
	var oCell = FCKSelection.MoveToAncestorNode('TD') || FCKSelection.MoveToAncestorNode('TH');
	if (!oCell) {
		return;
	}
	
	FCKUndo.SaveUndoStep();
	
	var oTable = FCKTools.GetElementAscensor(oCell, 'TABLE');
	var aTableMap = FCKTableHandler._CreateTableMap(oTable);
	var nColIndex = FCKTableHandler._GetCellIndexSpan(aTableMap, oCell.parentNode.rowIndex, oCell);
	
	FCKTableHandler.InsertColumn(insertBefore);
	
	var aColGroups = oTable.getElementsByTagName('COLGROUP');
	var aCols;
	if (!aColGroups.length) {
		return;
	}
	
	if (!insertBefore) {
		nColIndex++;
	}
	
	var oCol = FCK.EditorDocument.createElement('COL');
	for (i = 0; i < aColGroups.length; i++) {
		aCols = aColGroups[i].getElementsByTagName('COL');
		if (aCols.length <= nColIndex) {
			aColGroups[i].appendChild(oCol);
		}
		else {
			aColGroups[i].insertBefore(oCol, aCols[nColIndex + 1]);
		}
	}
	
	FCKUndo.SaveUndoStep();
}


//	列削除
FCKCommands.GetCommand('TableDeleteColumns').Execute = function() {
	var oCell = FCKSelection.MoveToAncestorNode('TD') || FCKSelection.MoveToAncestorNode('TH');
	if (!oCell) {
		return;
	}
	
	FCKUndo.SaveUndoStep();
	
	var oTable = FCKTools.GetElementAscensor(oCell, 'TABLE');
	var aTableMap = FCKTableHandler._CreateTableMap(oTable);
	var nColIndex = FCKTableHandler._GetCellIndexSpan(aTableMap, oCell.parentNode.rowIndex, oCell);
	
	FCKTableHandler.DeleteColumns();
	
	var aColGroups = oTable.getElementsByTagName('COLGROUP');
	var aCols;
	if (!aColGroups.length) {
		return;
	}
	
	for (i = 0; i < aColGroups.length; i++) {
		aCols = aColGroups[i].getElementsByTagName('COL');
		aColGroups[i].removeChild(aCols[nColIndex]);
	}
	
	FCKUndo.SaveUndoStep();
}


//	右のセルと連結
FCKCommands.RegisterCommand('TableMergeRightCell', {
	Execute : function() {
		var oCell = FCKSelection.MoveToAncestorNode('TD') || FCKSelection.MoveToAncestorNode('TH');
		var oRow = oCell.parentNode;
		if (!oCell || (oCell.cellIndex + 1 == oRow.cells.length)) {
			return;
		}
		
		var oRightCell = oRow.cells[oCell.cellIndex + 1];
		if (oCell.rowSpan != oRightCell.rowSpan) {
			return;
		}
		
		FCKUndo.SaveUndoStep();
		
		oCell.innerHTML += oRightCell.innerHTML;
		oCell.colSpan += oRightCell.colSpan;
		oRow.removeChild(oRightCell);
		
		FCKUndo.SaveUndoStep();
	},
	
	GetState : function() { return FCK_TRISTATE_OFF; }
})


//	下のセルと連結
FCKCommands.RegisterCommand('TableMergeLowerCell', {
	Execute : function() {
		var oCell = FCKSelection.MoveToAncestorNode('TD') || FCKSelection.MoveToAncestorNode('TH');
		var oRow = oCell.parentNode;
		if (!oCell || (oRow.rowIndex + oCell.rowSpan == oRow.parentNode.rows.length)) {
			return;
		}
		
		var oTable = FCKTools.GetElementAscensor(oCell, 'TABLE');
		var aTableMap = FCKTableHandler._CreateTableMap(oTable);
		var nColIndex = FCKTableHandler._GetCellIndexSpan(aTableMap, oRow.rowIndex, oCell);
		var nLowerRow = oRow.rowIndex + oCell.rowSpan;
		var oLowerCell = aTableMap[nLowerRow][nColIndex];
		
		if (!oLowerCell || oCell.colSpan != oLowerCell.colSpan) {
			return;
		}

		FCKUndo.SaveUndoStep();
		
		oCell.rowSpan += oLowerCell.rowSpan;
		oCell.innerHTML += oLowerCell.innerHTML;
		oLowerCell.parentNode.removeChild(oLowerCell);
		
		FCKUndo.SaveUndoStep();
	},
	
	GetState : function() { return FCK_TRISTATE_OFF; }
})


//	セルを左右に分割
FCKCommands.RegisterCommand('TableSplitCellRightLeft', {
	Execute : function() {
		var oCell = FCKSelection.MoveToAncestorNode('TD') || FCKSelection.MoveToAncestorNode('TH');
		if (!oCell || oCell.colSpan == 1) {
			return;
		}
		
		FCKUndo.SaveUndoStep();
		
		var oRightCell = FCK.EditorDocument.createElement('TD');
		oRightCell.className = 'style_td';
		if (FCKBrowserInfo.IsGeckoLike) {
			FCKTools.AppendBogusBr(oRightCell);
		}
		
		if (oCell.cellIndex == oCell.parentNode.cells.length - 1) {
			oCell.parentNode.appendChild(oRightCell);
		}
		else {
			oCell.parentNode.insertBefore(oRightCell, oCell.nextSibling);
		}
		oCell.colSpan--;
		
		FCKUndo.SaveUndoStep();
	},
	
	GetState : function() { return FCK_TRISTATE_OFF; }
})


//	セルを上下に分割
FCKCommands.RegisterCommand('TableSplitCellTopBottom', {
	Execute : function() {
		var oCell = FCKSelection.MoveToAncestorNode('TD') || FCKSelection.MoveToAncestorNode('TH');
		if (!oCell || oCell.rowSpan == 1) {
			return;
		}
		
		FCKUndo.SaveUndoStep();
		
		var oNewCell = FCK.EditorDocument.createElement('TD');
		oNewCell.className = 'style_td';
		if (FCKBrowserInfo.IsGeckoLike) {
			oEditor.FCKTools.AppendBogusBr(oNewCell);
		}
		
		var oTable = FCKTools.GetElementAscensor(oCell, 'TABLE');
		var aTableMap = FCKTableHandler._CreateTableMap(oTable);
		var oRow = oCell.parentNode;
		var nColIndex = FCKTableHandler._GetCellIndexSpan(aTableMap, oRow.rowIndex, oCell);
		var oLowerCell = null;
		
		for (i = nColIndex + 1; i < aTableMap[0].length; i++) {
			oLowerCell = aTableMap[oRow.rowIndex + 1][i];
			if (oLowerCell.parentNode.rowIndex == oRow.rowIndex + 1) {
				break;
			}
			oLowerCell = null;
		}

		if (!oLowerCell) {
			oTable.rows[oRow.rowIndex + 1].appendChild(oNewCell);
		}
		else {
			oLowerCell.parentNode.insertBefore(oNewCell, oLowerCell);
		}
		oCell.rowSpan--;
		
		FCKUndo.SaveUndoStep();
	},
	
	GetState : function() { return FCK_TRISTATE_OFF; }
})



///////////////////////////////////////////////////////////
//	ツールバー

FCK.Events.AttachEvent('OnSelectionChange', function () { FCKToolbarItems.GetItem('Table').RefreshState() });



///////////////////////////////////////////////////////////
//	コンテキストメニュー

FCK.ContextMenu.RegisterListener( {
	AddItems : function(menu, tag, tagName) {
		var bIsTable = (tagName == 'TABLE');
		var bIsCell = (!bIsTable && FCKSelection.HasAncestorNode('TABLE'));
		
		if (bIsCell) {
			menu.AddSeparator();
			var oItem = menu.AddItem('Cell', FCKLang.CellCM);
			oItem.AddItem('TableInsertCellBefore', FCKLang.InsertCellBefore, 69);
			oItem.AddItem('TableInsertCellAfter', FCKLang.InsertCellAfter, 58);
			oItem.AddItem('TableDeleteCells', FCKLang.DeleteCells, 59);
			if ( FCKBrowserInfo.IsGecko ) {
				oItem.AddItem( 'TableMergeCells'	, FCKLang.MergeCells, 60,
					FCKCommands.GetCommand( 'TableMergeCells' ).GetState() == FCK_TRISTATE_DISABLED ) ;
			}
			else {
				oItem.AddItem('TableMergeRightCell', FCKLang.MergeRight, 60);
				oItem.AddItem('TableMergeLowerCell', FCKLang.MergeDown, 60);
			}
			oItem.AddItem('TableSplitCellRightLeft', FCKLang.HorizontalSplitCell, 61);
			oItem.AddItem('TableSplitCellTopBottom', FCKLang.VerticalSplitCell, 61);
			oItem.AddSeparator();
			oItem.AddItem('TableCellProp', FCKLang.CellProperties, 57);

			menu.AddSeparator();
			oItem = menu.AddItem('Row', FCKLang.RowCM);
			oItem.AddItem('TableInsertRowBefore', FCKLang.InsertRowBefore, 70);
			oItem.AddItem('TableInsertRowAfter', FCKLang.InsertRowAfter, 62);
			oItem.AddItem('TableDeleteRows', FCKLang.DeleteRows, 63);
			
			menu.AddSeparator();
			oItem = menu.AddItem('Column', FCKLang.TableColMenu);
			oItem.AddItem('TableInsertColumnBefore', FCKLang.InsertColumnBefore, 71);
			oItem.AddItem('TableInsertColumnAfter', FCKLang.InsertColumnAfter, 64);
			oItem.AddItem('TableDeleteColumns', FCKLang.DeleteColumns, 65);
			oItem.AddSeparator();
			oItem.AddItem('TableCol', FCKLang.TableColDlgTitle);
		}

		if (bIsTable || bIsCell) {
			menu.AddSeparator();
			menu.AddItem('TableDelete', FCKLang.TableDelete);
			menu.AddItem('Table', FCKLang.TableProperties, 39);
		}
	}}
);


