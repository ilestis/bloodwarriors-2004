<?php
function bw_afficheToolbar($submit, $text = '', $titleZone = false, $title = '')
{

	?>
	<table class="tbl_message">
	<tr>
		<td rowspan="2" width="40px" valign="top">
		<?php
		//Smileys
		$sqlsmileys = "SELECT * FROM `autres_smileys` ORDER BY `id_smiley` ASC";
		$reqsmileys = mysql_query($sqlsmileys);
		$cpt = 0;
		while($ressmileys = mysql_fetch_array($reqsmileys))
		{
			echo "<a href=\"javascript:emoticon(document.getElementById('commentaire'), '".$ressmileys['code']."')\"><img src=\"smiles/icon_".$ressmileys['url_adresse'].".png\" border=\"0\"/></a>";
			$cpt ++;
			echo ($cpt % 2 == 0 ? "<br />\n": "&nbsp");
		}
		?>
		</td>
		<td>
		
		<?php
		# Title Zone?
		if($titleZone) {
			echo "
		<strong>Titre :</strong> <input type=\"text\" name=\"title\" maxlength=\"25\" style=\"width:80%\" value=\"".$title."\" /><br />";
		} ?>	
		
		<strong>Mise en forme :</strong><br />
		<textarea rows="15" style="width:100%" name="commentaire" id="commentaire"><?php echo $text; ?></textarea>

		<script type="text/javascript" src="include/toolbar.js"></script>

		<script type="text/javascript">
			if (document.getElementById) {
				var tb = new dcToolBar(document.getElementById('commentaire'),
				document.getElementById('commentaire'),'images/icons/');
				
				tb.btStrong('Forte emphase');
				tb.btEm('Emphase');
				tb.btUm('Souligné');
				tb.btSt('Barré');
				tb.btColor('Couleur');
				tb.addSpace(10);
				tb.btQuote('Citation');
				tb.btImg('Image');
				tb.btUrl('URL');
				tb.addSpace(10);
				tb.draw("Mise en forme:");
			}
			</script>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align:right;"><input class="valide" type="submit" value="<?php echo $submit; ?>" /></td>
	</tr>
	</table>
<?php
}
?>