<?php
/*----------------------[TABLEAU]---------------------
|Nom:			Function_mef.PHP
+-----------------------------------------------------
|Description:	Fonctions des mise en forme
+-----------------------------------------------------
|Date de création:				jj/mm/aa
|Date du premier test:			jj/mm/aa
|Dernière modification:			07.02.2006
+---------------------------------------------------*/


/*---------------------------------------------------------------*/
//-------------------------------NEW-----------------------------//
/*---------------------------------------------------------------*/

function clean($Text)
{//Nettoie ce qu'on envoit
	$Text = str_replace('<', '&lt;',$Text);
	$Text = str_replace('>', '&gt;',$Text);
	$Text = str_replace("'", "&#039;", $Text);
	$Text = str_replace('"', "&quot;", $Text);
	$Text = trim($Text);

	return $Text;
}

function forumadd($Text)
{
	//Enlève tout le chenni (<, >, ', / etc...
	$Text = str_replace('<', '&lt;',$Text);
	$Text = str_replace('>', '&gt;',$Text);
	$Text = str_replace("'", "&#039;", $Text);
	$Text = str_replace('\\"', "&quot;", $Text);
	$Text = str_replace('"', "&quot;", $Text);

	return $Text;
}

function forummessage($comment)
{
	//Mise en forme
	$comment = preg_replace('`\[b](.*?)\[/b]`si', '<strong>$1</strong>', $comment);
	$comment = preg_replace('`\[u](.*?)\[/u]`si', '<ins>$1</ins>', $comment);
	$comment = preg_replace('`\[s](.*?)\[/s]`si', '<del>$1</del>', $comment);
	$comment = preg_replace('`\[i](.*?)\[/i]`si', '<em>$1</em>', $comment);
	$comment = preg_replace('`\[img](.*?)\[/img]`si', '<img src="$1">', $comment);

	/*$comment = preg_replace('`\[b](.*?)\[/b]`si', '<strong>$1</strong>', $comment);
	$comment = preg_replace('`\[u](.*?)\[/u]`si', '<U>$1</U>', $comment);
	$comment = preg_replace('`\[i](.*?)\[/i]`si', '<em>$1</em>', $comment);
	$comment = preg_replace('`\[center](.*?)\[/center]`si', '<div style="text-align: center; margin: 0px;">$1</div>', $comment);*/
	$comment = preg_replace('`\[img](.*?)\[/img]`si', '<img src="$1">', $comment);
	$comment = quote_callback($comment);
	//$comment = preg_replace('`\[quote=&quot;(.+)&quot;](.*?)\[/quote]`si', '<fieldset class="fquote"><legend>$1&nbsp;a dit:</legend>$2</fieldset>', $comment);
	$comment = preg_replace("`\[url=&quot;(.+)&quot;](.*?)\[/url]`si", '<a href="$1" target="_Blank">$2</a>', $comment);


	$comment = preg_replace('`\[color=&quot;#(.*?)&quot;](.+)\[/color]`si', '<span style="color:#$1;">$2</span>', $comment);
	//$comment = preg_replace('!http://[a-z0-9._/-]+!i', '<a href="$0" target="_Blank">$0</a>', $comment);

	$comment = str_replace("[ligne]", "<hr>", $comment);
	//$comment = str_replace("[br]", "<br>", $comment);
	/*$comment = str_replace("[i]", "<em>", $comment);
	$comment = str_replace("[/i]", "</em>", $comment);*/

	//$comment = preg_replace("!\[quote=&quot;(.+)&quot;\](.+)\[/quote\]!", '<TABLE width="400px" bgcolor="#D1D1D1"><tr><td bgcolor="#D1D1D1" colspan="2">$1&nbsp;a dit:</td></tr><tr><td bgcolor="#D1D1D1" width="20px">&nbsp;</td><td bgcolor="#D1D1D1" width="480px"><I>$2</I></td></tr></table>', $comment);

	//Smileys
	$smi = "SELECT * FROM `autres_smileys` ORDER BY `id_smiley` ASC";
	$smip = sql_query($smi);
	while($smis = mysql_fetch_array($smip))
	{
		$comment = str_replace($smis['code'],'<img src="smiles/icon_'.$smis['url_adresse'].'.png">',$comment);
	}

	//Eval?
	if(isset($_SESSION['id_joueur']) && $_SESSION['aut'][13] != '1') {
		$comment = preg_replace('`\[eval](.*?)\[/eval]`si', '$1', $comment);
		$comment = preg_replace('`\[bwvar](.*?)\[/bwvar]`si', '$1', $comment);
	}

	return $comment;

}
function quote_callback($text)
{
	//&quote;
	$regex = '`\[quote=(.*?)\](.+)\[/quote\]`si';
	if(is_array($text)) $text = '<fieldset class="fquote"><legend>'.$text[1].'&nbsp;a dit:</legend>'.$text[2].'</fieldset>';
	return preg_replace_callback($regex, 'quote_callback', $text);
}

function reversemessage($Text)
{//Retourne l'inverse 
	$Text = str_replace('&lt;','<',$Text);
	$Text = str_replace('&gt;','>',$Text);
	$Text = str_replace("&#039;", "'", $Text);
	$Text = str_replace("&quot;", '"', $Text);

	//Smileys
	$smi = "SELECT * FROM `autres_smileys` ORDER BY `id_smiley` ASC";
	$smip = sql_query($smi);
	while($smis = mysql_fetch_array($smip))
	{
		$Text = str_replace('<img src="smiles/icon_'.$smis['url_adresse'].'.png">', $smis['code'], $Text);
	}

	//Mise en forme
	$Text = quote_callback_return($Text);
	//$Text = preg_replace('`<fieldset class="fquote"><legend>(.*?)&nbsp;a dit:</legend>(.*?)</fieldset>`si', "[quote=\"$1\"]$2[/quote]", $Text);

	//$comment = preg_replace('`\[quote=&quot;(.+)&quot;](.*?)\[/quote]`si', '<p class="quoteTitle">$1&nbsp;a dit:<p class="quoteContent$2</p></p>', $comment);


	$Text = preg_replace('`<strong>(.*?)</strong>`si', '[b]$1[/b]', $Text);
	$Text = preg_replace('`<em>(.*?)</em>`si', '[i]$1[/i]', $Text);
	$Text = preg_replace('`<ins>(.*?)</ins>`si', '[u]$1[/u]', $Text);
	$Text = preg_replace('`<del>(.*?)</del>`si', '[st]$1[/st]', $Text);
	$Text = preg_replace('`<div style="text-align: center; margin: 0px;">(.*?)</div>`si', '[center]$1[/center]', $Text);
	$Text = preg_replace('`<img src="(.*?)">`si', '[imagebw]$1[/imagebw]', $Text);
	$Text = preg_replace('`<a href="(.*?)" target="_Blank">(.+)</a>`si', '[url]$1[/url]', $Text);
	
	$Text = preg_replace('`<span style="color:(.*?);">(.*?)</span>`si', '[color:$1]$2[/color]', $Text);

	
	$Text = str_replace("<hr>", "[ligne]", $Text);
	$Text = str_replace("<br>", "[br]", $Text);
	$Text = str_replace("<br />", "[br]", $Text);
	/*$Text = str_replace("<i>", "[i]", $Text);
	$Text = str_replace("</i>", "[/i]", $Text);*/
	
	//Retourne le résultat
	return $Text;
}
function quote_callback_return($text)
{
	$regex = '`<fieldset class="fquote"><legend>(.*?)&nbsp;a dit:</legend>(.+)</fieldset>`si';
	if(is_array($text)) $text = '[quote='.$text[1].']'.$text[2].'[/quote]';
	return preg_replace_callback($regex, 'quote_callback_return', $text);
}

function affiche_variables_callback($text)
{
	$regex = '`\[bwvar\](.+)\[/bwvar\]`si';

	if(is_array($text)) {
		if(!empty($GLOBALS['CONF'][$text[1]])) $text = $GLOBALS['CONF'][$text[1]];
		else $text = $text[1];
	}

	return preg_replace_callback($regex, 'affiche_variables_callback', $text);
}


function affiche($comment)
{
	//$comment = stripslashes($comment);
	$comment = affiche_variables_callback($comment);
	$comment = nl2br($comment);

	return $comment;
}
?>