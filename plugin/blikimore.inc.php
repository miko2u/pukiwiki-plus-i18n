<?php
/*
 * $Id$
 * 
 * License:  GNU General Public License
 *
 * Copyright (c) 2005 in3c.org
 * Portions Copyright (c) 2004 ようか
 *   http://noldor.info/
 *   http://kinowiki.net/
 *
 * MODIFICATION BY:
 * (C) 2006 PukiWiki Plus! Developers Team
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,
 * USA.
 * 
 */

/**
 * 書式
 *      #blikimore
 *
 * 種別 
 *      ブロック型プラグイン
 *  
 * 概要
 *      <a name="more"></a>を表示する
 * 
 * @author Yuki SHIDA <shida@in3c.org>
 */
function plugin_blikimore_convert()
{
    return '<a name="more"></a>'."\n";
}
?>
