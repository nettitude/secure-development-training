 	function insert_at_position(mygalTag) {
		mceWindow = window.opener;
		if(mceWindow.tinyMCE) {
			mceWindow.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, mygalTag);
		} else {
			edCanvas = mceWindow.document.getElementById('content');
			mceWindow.edInsertContent(edCanvas, mygalTag);
		}
		window.close();
	}

function insert_wrapper(picid) {
	
	
	var myalign=document.getElementById('picalign['+picid+']').value;
	var mypopup=document.getElementById('popup['+picid+']').value;
	var mypicsize=document.getElementById('picsize['+picid+']').value;	
	var mypicsizeradio1=document.getElementsByName('picsize'+picid)[0].checked;
	var mypicsizeradio2=document.getElementsByName('picsize'+picid)[1].checked;
	var mylightboxgroup=document.getElementsByName('lightboxgroup'+picid)[1].checked;
	var inspic='';
	var lightboxgname='';

	if (myalign=='none') {
		myalign='';
	}

	if (mypopup=='none') {
		mypopup='';
	}
	
	if (mypicsizeradio1==true) {
		mypicsize='0';
	}
	else if (mypicsizeradio2==true) {
		mypicsize='thumb';
	}
	
	if (mylightboxgroup==true) {
		lightboxgname=',:'+document.getElementById('lightboxgroup['+picid+']').value;
	}
	
	inspic='[inspic='+picid+','+myalign+','+mypopup+','+mypicsize+lightboxgname+']';
	
	 insert_at_position(inspic);
}

function linktext_wrapper(picid,message) {
	
	var picref='';
	var linktext = prompt(message,'');
	if (linktext !=null) {
		picref='[mypicref='+picid+']'+linktext+'[/mypicref]';
		insert_at_position(picref);
	}
}

function showfieldset(myfieldset,mybuttonshow,mybuttonhide) {
		
	document.getElementById(myfieldset).style.display ='block' ;
	document.getElementById(mybuttonhide).style.display ='none' ;
	document.getElementById(mybuttonshow).style.display ='inline' ;
}


function hidefieldset(myfieldset,mybuttonshow,mybuttonhide) {
	
	document.getElementById(myfieldset).style.display ='none' ;
	document.getElementById(mybuttonhide).style.display ='none' ;
	document.getElementById(mybuttonshow).style.display ='inline' ;
}
