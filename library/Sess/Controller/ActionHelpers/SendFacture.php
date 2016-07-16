<?php

class Sess_Controller_ActionHelpers_SendFacture extends Zend_Controller_Action_Helper_Abstract
{
	
	protected function getWrappedText($string, Zend_Pdf_Style $style,$max_width)
	{
		$wrappedText = '' ;
		$lines = explode("\n",$string) ;
		foreach($lines as $line) {
			$words = explode(' ',$line) ;
			$word_count = count($words) ;
			$i = 0 ;
			$wrappedLine = '' ;
			while($i < $word_count)
			{
				/* if adding a new word isn't wider than $max_width,
				 we add the word */
				if($this->widthForStringUsingFontSize($wrappedLine.' '.$words[$i]
						,$style->getFont()
						, $style->getFontSize()) < $max_width) {
					if(!empty($wrappedLine)) {
						$wrappedLine .= ' ' ;
					}
					$wrappedLine .= $words[$i] ;
				} else {
					$wrappedText .= $wrappedLine."\n" ;
					$wrappedLine = $words[$i] ;
				}
				$i++ ;
			}
			$wrappedText .= $wrappedLine."\n" ;
		}
		return $wrappedText ;
	}
	
	/**
	 * found here, not sure of the author :
	 * http://devzone.zend.com/article/2525-Zend_Pdf-tutorial#comments-2535
	 */
	protected function widthForStringUsingFontSize($string, $font, $fontSize)
	{
		$drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
		$characters = array();
		for ($i = 0; $i < strlen($drawingString); $i++) {
			$characters[] = (ord($drawingString[$i++]) << 8 ) | ord($drawingString[$i]);
		}
		$glyphs = $font->glyphNumbersForCharacters($characters);
		$widths = $font->widthsForGlyphs($glyphs);
		$stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
		return $stringWidth;
	}
	
	public function send($order,$type,$email)
	{
		$nbpage = 1;
		$total = 0;
		$total_ht = 0;
		$commandes = new Customer_Model_TOrdersd();
		$details = $commandes->getDetails($order);
		$details_all = $commandes->getDetailsAll($order);
		$orders = new Customer_Model_TOrders();
		$solde = $orders->getSolde($order);
		
		$leDocumentPDF = new Zend_Pdf();
		$laPage = $leDocumentPDF->newPage(Zend_Pdf_Page::SIZE_A4);
		$laPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 10);
		$header = Zend_Pdf_Image::imageWithPath('img/admin/factures/logo_fac.jpg');
		$laPage->drawImage($header,  150, 717, 426, 841);
		$laPage->drawText("SARL ESCARGOUT", 50, 720);
		$laPage->drawText("paniersprimeurs.escargout@gmail.com", 50, 700);
		$laPage->drawText("SIRET : 4931017200019", 50, 680);
		$laPage->drawText("N° TVA : FR74493102172", 50, 660,'UTF-8');
		if($details[0]->SOCIETE != 'PUBLIC'):
			$laPage->drawText($details[0]->SOCIETE, 350, 640,'UTF-8');
		endif;
		$laPage->drawText($details[0]->NOM, 350, 620,'UTF-8');
		
		$date = new Zend_Date($details[0]->DATE,Zend_Date::ISO_8601);
		$laPage->drawText("Date : ", 50, 620);
		$laPage->drawText($date->toString('dd/MM/yyyy'), 80, 620);
		
		if($type=="FAC"):
			$laPage->drawText("Facture N° ".sprintf('%08d',$details[0]->IDORDER)."", 50, 600,'UTF-8');
		else:
			$laPage->drawText("Commande N° ".sprintf('%08d',$details[0]->IDORDER)."", 50, 600,'UTF-8');
		endif;	
		$laPage->drawText("Point de livraison : ".$details[0]->LIEU."", 50, 580);
		$laPage->drawText("Jours de livraison : ".$details[0]->DATEL."", 50, 560,'UTF-8');
		$laPage->drawText("Paiement : ".$details[0]->PAIEMENT."",50 , 540);
		
		$laPage->setLineColor(Zend_Pdf_Color_Html::color('#D5D5D5'));
		$laPage->setLineDashingPattern(array(3, 2, 3, 4), 1.6);
		
		$laPage->drawLine(30, 520, 570, 520);//LIGNE HAUT
		$laPage->drawLine(30, 520, 30, 520);//LIGNE GAUCHE
		$laPage->drawLine(570, 520, 570, 520);//LIGNE DROITE
			
		$laPage->drawLine(30, 490, 570, 490);//LIGNE BAS
		
		$laPage->drawText("Article(s)", 50, 500);
		$laPage->drawText("Quantité", 200, 500,'UTF-8');
		$laPage->drawText("Unit TTC", 275, 500);
		$laPage->drawText("TVA", 350, 500);
		$laPage->drawText("Montant HT", 425, 500);
		$laPage->drawText("Montant TTC", 500, 500);
		$y = 470;
		$laPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 10);
		$i=0;
		$leDocumentPDF->pages[1] = $laPage;
		foreach ($details as $detail):
			if(($detail->IDPANIER !=0 && $detail->IDPRODUIT == 0) || ($detail->IDPANIER == 0 && $detail->IDPRODUIT != 0)):
				if($i==14):
					$laPage->drawLine(30, 520, 30, 0);//LIGNE GAUCHE
					$laPage->drawLine(570, 520, 570, 0);//LIGNE DROITE
					$laPage->drawLine(195, 520, 195, 0);//LIGNE COL 1
					$laPage->drawLine(270, 520, 270, 0);//LIGNE COL 2
					$laPage->drawLine(345, 520, 345, 0);//LIGNE COL 3
					$laPage->drawLine(420, 520, 420, 0);//LIGNE COL 4
					$laPage->drawLine(495, 520, 495, 0);//LIGNE COL 4
					$nbpage +=1;
					$leDocumentPDF->pages[$nbpage] = $laPage;
					$laPage = $leDocumentPDF->newPage(Zend_Pdf_Page::SIZE_A4);
					$laPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 10);
					$laPage->setLineColor(Zend_Pdf_Color_Html::color('#D5D5D5'));
					$laPage->setLineDashingPattern(array(3, 2, 3, 4), 1.6);
					$y = 780;
					$i=0;
				endif;
		
				$laPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 8);
				$laPage->drawText($detail->UNITE_V, 225, $y,'UTF-8');
				$laPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 10);
				$laPage->drawText($detail->QTE, 200, $y);
				$laPage->drawText($detail->PRIX_UNIT, 275, $y);
				$laPage->drawText($detail->TVA,350, $y);
				$laPage->drawText(number_format(($detail->PRIX_UNIT*$detail->QTE)-($detail->PRIX_UNIT*$detail->QTE*$detail->TVA/100),2), 425, $y);
				$laPage->drawText(number_format($detail->PRIX_UNIT*$detail->QTE,2), 500, $y);
					
				$style = new Zend_Pdf_Style();
				$style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 10);
				$lines = explode("\n",$this->getWrappedText('- '.$detail->PRODUIT, $style, 140));
				$laPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 10);
				foreach ($lines as $line):
					$laPage->drawText($line, 50, $y,'UTF-8');
					if(count($lines)>1):
						$y = $y-10;
					endif;
				endforeach;
			
				//$y = $y-30;
				$total += $detail->PRIX_UNIT*$detail->QTE;
				$total_ht += ($detail->PRIX_UNIT*$detail->QTE)-($detail->PRIX_UNIT*$detail->QTE*$detail->TVA/100);
				$i++;
			endif;
		endforeach;
		
		$produitnondispos = $commandes->getOrderUnavailable($details[0]->IDORDER);
		foreach ($produitnondispos as $dispo):
			$laPage->setFillColor(Zend_Pdf_Color_Html::color('#FF0000'));
			$lines = explode("\n",$this->getWrappedText('- '.$dispo->PRODUIT, $style, 140));
			foreach ($lines as $line):
				$laPage->drawText($line, 50, $y,'UTF-8');
				if(count($lines)>1):
					$y = $y-10;
				endif;
			endforeach;
			$laPage->drawText("non disponible", 50, $y);
			$total += $dispo->PRIX_UNIT*$dispo->QTE;
			$total_ht += ($dispo->PRIX_UNIT*$dispo->QTE)-($dispo->PRIX_UNIT*$dispo->QTE*$dispo->TVA/100);
			$y = $y-20;
		endforeach;
		$laPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 10);
		$laPage->setFillColor(Zend_Pdf_Color_Html::color('#000000'));
		
		$y -= 10;
		$laPage->drawLine(30, $y+15, 570, $y+15); //LIGNE HAUT
		$laPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 10);
		if($details[0]->FRAIS != '0'):
			$laPage->drawText("FRAIS", 50, $y);
			$laPage->drawText($details[0]->FRAIS." €", 500, $y);
			$y = $y-20;
			$poucent_frais = $details[0]->FRAIS*100/$total;
		endif;
		if($details[0]->REMISE != '0'):
			$laPage->drawText("REMISE", 50, $y);
			$laPage->drawText($details[0]->REMISE." %", 500, $y);
			$y = $y-20;
			$poucent_remise = $details[0]->REMISE;
		endif;
		if($details[0]->PROMO != '0'):
			$laPage->drawText("PROMO", 50, $y);
			$laPage->drawText($details[0]->PROMO." %", 500, $y);
			$y = $y-20;
			$poucent_promo = $details[0]->PROMO;
			endif;
		if($details[0]->FIDELITE != '0'):
			$laPage->drawText("FIDELITE", 50, $y);
			$laPage->drawText($details[0]->FIDELITE." €", 500, $y);
			$y = $y-20;
			$poucent_fidelite = $details[0]->FIDELITE*100/$total;
		endif;
		$reglements = new Customer_Model_TReglements();
		$avoirs = $reglements->getOrderavoirs($order);
		if($avoirs->AVOIR != '0' && $avoirs->AVOIR != ''):
			$laPage->drawText("AVOIR", 50, $y);
			$laPage->drawText($avoirs->AVOIR." €", 500, $y);
			$y = $y-20;
			$poucent_avoirs = $avoirs->AVOIR*100/$total;
		endif;
			
		foreach ($details_all as $detail):
			if(($detail->IDPANIER !=0 && $detail->IDPRODUIT == 0) || ($detail->IDPANIER == 0 && $detail->IDPRODUIT != 0)):
				//FIDELITE
				if(isset($poucent_fidelite)):
					$laPage->drawText($prix_unit, 455, $y);
					$fidelite_ht += number_format(($poucent_fidelite*($detail->PRIX_UNIT*$detail->QTE)/100)-(($poucent_fidelite*($detail->PRIX_UNIT*$detail->QTE)/100)*$detail->TVA/100),8);
					$fidelite += number_format(($poucent_fidelite*($detail->PRIX_UNIT*$detail->QTE)/100),8);
				endif;
				//FRAIS
				if(isset($poucent_frais)):
					$laPage->drawText($prix_unit, 455, $y);
					$frais_ht += number_format(($poucent_frais*($detail->PRIX_UNIT*$detail->QTE)/100)-(($poucent_frais*($detail->PRIX_UNIT*$detail->QTE)/100)*$detail->TVA/100),8);
					$frais += number_format(($poucent_frais*($detail->PRIX_UNIT*$detail->QTE)/100),8);
				endif;
				//PROMO
				if(isset($poucent_promo)):
					$laPage->drawText($prix_unit, 455, $y);
					$promo_ht += number_format(($poucent_promo*($detail->PRIX_UNIT*$detail->QTE)/100)-(($poucent_promo*($detail->PRIX_UNIT*$detail->QTE)/100)*$detail->TVA/100),8);
					$promo += number_format(($poucent_promo*($detail->PRIX_UNIT*$detail->QTE)/100),8);
				endif;
				//REMISE
				if(isset($poucent_remise)):
					$laPage->drawText($prix_unit, 455, $y);
					$remise_ht += number_format(($poucent_remise*($detail->PRIX_UNIT*$detail->QTE)/100)-(($poucent_remise*($detail->PRIX_UNIT*$detail->QTE)/100)*$detail->TVA/100),8);
					$remise += number_format(($poucent_remise*($detail->PRIX_UNIT*$detail->QTE)/100),8);
				endif;
				//AVOIR
				if(isset($poucent_avoirs)):
					$laPage->drawText($prix_unit, 455, $y);
					$avoir_ht += number_format(($poucent_avoirs*($detail->PRIX_UNIT*$detail->QTE)/100)-(($poucent_avoirs*($detail->PRIX_UNIT*$detail->QTE)/100)*$detail->TVA/100),8);
					$avoir += number_format(($poucent_avoirs*($detail->PRIX_UNIT*$detail->QTE)/100),8);
				endif;
			endif;
		endforeach;
			
		$total_ht = $total_ht-$fidelite_ht+$frais_ht-$promo_ht-$remise_ht-$avoir_ht;
		$total = $total-$fidelite+$frais-$promo-$remise-$avoir;
		if($total <= '0'):
			$total_ht = '0.00';
			$total = '0.00';
		endif;
			
		$laPage->drawText("TOTAL", 50, $y);
		$laPage->drawText(number_format($total_ht,2).' €', 425, $y);
		$laPage->drawText(number_format($total,2).' €', 500, $y);
		$y = $y-20;
		$laPage->drawText("RESTANT DU", 50, $y);
		$laPage->drawText(number_format($solde->TOTAL-$solde->RAP,2).' €', 500, $y);
			
		if($nbpage>1):
			$laPage->drawLine(195, 800, 195, $y-15);//LIGNE COL 1
			$laPage->drawLine(270, 800, 270, $y-15);//LIGNE COL 2
			$laPage->drawLine(345, 800, 345, $y-15);//LIGNE COL 3
			$laPage->drawLine(420, 800, 420, $y-15);//LIGNE COL 4
			$laPage->drawLine(495, 800, 495, $y-15);//LIGNE COL 4
			$laPage->drawLine(30, 800, 30, $y-15);//LIGNE GAUCHE
			$laPage->drawLine(570, 800, 570, $y-15);//LIGNE DROITE
		else:
			$laPage->drawLine(195, 520, 195, $y-15);//LIGNE COL 1
			$laPage->drawLine(270, 520, 270, $y-15);//LIGNE COL 2
			$laPage->drawLine(345, 520, 345, $y-15);//LIGNE COL 3
			$laPage->drawLine(420, 520, 420, $y-15);//LIGNE COL 4
			$laPage->drawLine(495, 520, 495, $y-15);//LIGNE COL 4
			$laPage->drawLine(30, 520, 30, $y-15);//LIGNE GAUCHE
			$laPage->drawLine(570, 520, 570, $y-15);//LIGNE DROITE
		endif;
		
		$laPage->drawLine(30, $y-15, 570, $y-15); //LIGNE BAS
		
		$footer = Zend_Pdf_Image::imageWithPath('img/admin/factures/footer_fac.jpg');
		//$laPage->drawImage($footer,  0, 0, 600, 133);
		
		$leDocumentPDF->pages[$nbpage] = $laPage;
		$leDocumentPDF->save("download/factures/Order-".sprintf('%08d',$order).".pdf");
		
		if($email=='YES'):
			$mail = new Zend_Mail("utf-8");
			if($type=='FAC'):
				$resultat = $mail->setBodyHtml('<p><h2>Bonjour,</h2></p><p>Nous vous remercions pour votre commande dont vous trouverez la facture en piece jointe.</p><p>Cordialement,</p><p>Paniers Primeurs Escargout.</p>');
			else:
				$resultat = $mail->setBodyHtml('<p><h2>Bonjour,</h2></p><p>Nous vous remercions pour votre commande dont vous trouverez le detail en piece jointe.</p><p>Cordialement,</p><p>Paniers Primeurs Escargout.</p>');
			endif;
			$resultat->setFrom('paniersprimeurs.escargout@gmail.com', 'Paniers Primeurs Escargout')
				//->addTo($details[0]->EMAIL, 'Paniers Primeurs Escargout')
				->addTo('contact@draw-web.fr', 'Paniers Primeurs Escargout')
				->setSubject('Paniers Primeurs Escargout');
				
			$filename = "download/factures/Order-".sprintf('%08d',$order).".pdf";
			$contents =  file_get_contents($filename);
			$at = new Zend_Mime_Part($contents);
			$at->type        = 'application/x-pdf';
			$at->disposition = Zend_Mime::DISPOSITION_INLINE;
			$at->encoding    = Zend_Mime::ENCODING_BASE64;
			$at->filename    = "Order-".sprintf('%08d',$order).".pdf";
			$mail->addAttachment($at);
			$mail->send();
		else:
			$file="download/factures/Order-".sprintf('%08d',$order).".pdf";
			return $file;
		endif;

	}
}