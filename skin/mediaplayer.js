
var Skins = new Array();

function FindSkin( control )
{
	for( var i = 0; i < Skins.length; i++ )
	{
		if( Skins[i].Contains( control ) )
		{
			return Skins[i];
		}
	}
	var skin = new MediaPlayerSkin( control );
	if( skin != null )
	{
		skin.Index = Skins.length;
		Skins[Skins.length] = skin;
	}
	return skin;
}

function PlayerSupported(control)
{
	// Only checks for script currently...
	return true;
}


function MediaPlayerSkin( control )
{
	this.Container = control;
	while( null != this.Container && this.Container.className != "playercontainer" )
	{
		this.Container = this.Container.offsetParent;
	}
	
	if( null == this.Container ) return null; // bad control, throw?
	var table = this.Container.children[1].children[1];
	this.Coverup = this.Container.children[0];
	this.Player = this.Container.children[1].children[0];
	this.PlayerMajorVersion = Number(this.Player.versionInfo.substring( 0, this.Player.versionInfo.indexOf( "." ) ) );
	
	supported = true;
	this.PlayButton = table.rows[0].cells[0].children[0];
	this.StopButton = table.rows[0].cells[1].children[0];
	this.Slider = table.rows[0].cells[2];
	this.MuteButton = table.rows[0].cells[3].children[0];
	this.LaunchButton = table.rows[0].cells[4].children[0];
	this.State = new MediaState( null, null, this );
	
	this.Contains = contains;
	function contains(control)
	{
		return this.Container.contains( control );
		/*return 
			this.PlayButton == control ||
			this.StopButton == control ||
			this.Slider == control ||
			this.MuteButton == control ||
			this.LaunchButton == control;*/
	}
	
	this.Play = play;
	function play()
	{
		this.Coverup.style.display = "none";
		this.Player.uiMode = "none";
		switch( this.Player.playState )
		{
			case 3 : // playing
				disable( this.StopButton );
				this.State.Pause();
				this.PlayButton.src = this.PlayButton.src.replace( "_pause_", "_play_" );
				break;
			default :			
				enable( this.StopButton );
				this.State.Play();
				this.PlayButton.src = this.PlayButton.src.replace( "_play_", "_pause_" );
				break;
		}
	}
	
	this.Stop = stop;
	function stop()
	{
		if( this.PlayerMajorVersion >= 9 ) 
		{
			this.Player.uiMode = "invisible";
		}
		this.Coverup.style.display = "inline";
		this.PlayButton.src = this.PlayButton.src.replace( "_pause_", "_play_" );
		this.State.Stop();
		disable(this.StopButton);
	}
	
	this.ToggleMute = toggleMute;
	function toggleMute()
	{
		this.Player.settings.mute = !this.Player.settings.mute;
		if( this.Player.settings.mute )
		{
			this.MuteButton.src = this.MuteButton.src.replace( "_on_", "_off_" );
		}
		else
		{
			this.MuteButton.src = this.MuteButton.src.replace( "_off_", "_on_" );
		}
	}
	
	this.LaunchPlayer = launchPlayer;
	function launchPlayer()
	{
		this.State.Stop();
		this.Player.openPlayer( this.Player.url );
	}
	
	this.AdjustSlider = adjustSlider;
	function adjustSlider(offset)
	{
		var width = offset - this.Slider.children[0].offsetLeft - ( this.Slider.children[1].offsetWidth / 2 );
		var availWidth = this.Slider.offsetWidth - this.Slider.children[1].offsetWidth - this.Slider.children[0].offsetLeft;
		this.State.PositionPercent = width / availWidth;
		this.State.SetPlayerPosition();
	}
	
	function enable(el)
	{
		el.src = el.src.replace("_d.","_n.");
	}

	function disable(el)
	{
		if( el.src.indexOf( "_h." ) != -1 ) el.src = el.src.replace("_h.","_d.");
		else el.src = el.src.replace("_n.","_d.");
	}
	
}

function MediaState( playerId, sliderId, skin )
{
	this.Skin = skin;
	if( null != skin ) this.Player = skin.Player;
	this.Interval = null;
	this.PositionPercent = 0.0;
	
	this.UpdatePosition = updatePosition;
	function updatePosition()
	{
		if( !this.EnsureControls() ) return;
		this.PositionPercent = this.Player.controls.currentPosition / this.Player.currentMedia.duration;
		this.SetSliderPosition();
	}
	
	this.SetPlayerPosition = setPlayerPosition;
	function setPlayerPosition()
	{
		if( !this.EnsureControls() ) return;
		this.Player.controls.currentPosition = this.PositionPercent * this.Player.currentMedia.duration;
		this.UpdatePosition();
	}
	
	this.SetSliderPosition = setSliderPosition;
	function setSliderPosition()
	{
		if( !this.EnsureControls() ) return;
		var totalWidth = this.Skin.Slider.offsetWidth - this.Skin.Slider.children[0].offsetLeft
		
		var availWidth = totalWidth - this.Skin.Slider.children[1].offsetWidth - 1;
		
		var width = this.PositionPercent * availWidth;
		var dlwidth = ( availWidth * this.Player.network.downloadProgress / 100 ) - width;
		
		if( this.PositionPercent >= 1 )
		{ 
			width = availWidth;
			dlwidth = 0;
			this.StopUpdates();
		}
		if( isNaN( width ) ) width = 0;
		if( dlwidth < 0 ) dlwidth = 0;
		if( this.Player.playState == 3 )
			this.Skin.Slider.children[2].style.width = Math.round( dlwidth );
		this.Skin.Slider.children[0].style.width = Math.round( width );
	}
	
	this.StopUpdates = stopUpdates;
	function stopUpdates()
	{
		if( null != this.Interval )
			clearInterval(this.Interval);
		this.Interval = null;
	}
	
	this.StartUpdates = startUpdates;
	function startUpdates()
	{
		if( !this.EnsureControls() ) return;
		this.UpdatePosition();
		this.Interval = setInterval( new Function( "UpdateState('" + this.Skin.Index + "');" ), 1000 );
	}
	
	this.EnsureControls = ensureControls;
	function ensureControls()
	{
		if( this.Skin == null )
		{	this.Skin = FindSkin( document.getElementById( this.PlayerId ) );
			if( this.Skin != null )
				this.Player = this.Skin.Player;
		}
		return this.Skin != null;
	}
	
	this.Pause = pause;
	function pause()
	{
		if( !this.EnsureControls() ) return;
		this.Player.controls.pause();
		this.StopUpdates();
	}
	
	this.Play = play;
	function play()
	{
		if( !this.EnsureControls() ) return;
		this.Player.controls.play();
		this.StartUpdates();
	}
	
	this.Stop = stop;
	function stop()
	{
		if( !this.EnsureControls() ) return;
		this.Player.controls.stop();
		this.StopUpdates();
		this.SetSliderPosition();
	}
	
}

function UpdateState(skinId)
{
	var skin = Skins[ skinId ];
	if( null == skin ) return;
	
	skin.State.UpdatePosition();
	if( skin.Player.playState == 1 ) skin.Stop();
	
}

function play(playerId,el)
{
	var skin = FindSkin( el );
	if( null == skin ) return;
	skin.Play();
	return false;
}

function stop(playerId,el)
{
	var skin = FindSkin( el );
	if( null == skin ) return;
	skin.Stop();
	return false;
}

function mute(playerId, el)
{
	var skin = FindSkin( el );
	if( null == skin ) return;
	skin.ToggleMute();
	return false;
}

function openPlayer(playerId, el)
{
	var skin = FindSkin( el );
	if( null == skin ) return;
	skin.LaunchPlayer();
	return false;
}
function slide( playerId, el )
{
	var skin = FindSkin( el );
	if( null == skin ) return;
	skin.AdjustSlider( event.offsetX );
	return false;
}

function handle( playerId,el)
{
	event.cancelBubble = true;
	var skin = FindSkin( el );
	if( null == skin ) return;
	var x = event.offsetX + el.offsetLeft;
	skin.AdjustSlider( x );
	return false;
}


function hover(el)
{
	if( el.src.indexOf( "_d." ) != -1 ) return;
	el.src = el.src.replace("_n.","_h.");
}

function out(el)
{
	if( el.src.indexOf( "_d." ) != -1 ) return;
	el.src = el.src.replace("_h.","_n.");
}