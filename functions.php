<?php
/*
Plugin Name: PDF List
Plugin URI: https://github.com/programmatically/wp-plugin-list-pdf-media-files
Description: List all PDF Files in your media library on a page and post also generates a PDF XML sitemap. Use [listpdfs] short code to link all PDF in your media library into a page or post or navigate to http://www.mywpinstall.com/pdf-sitemap.xml for the PDF XML sitemap.
Version: Version No 1.00
Author: wibblebuilder
Author URI: http://themes.technology
License: GPL v3
*/

// WB_Getpdflist -> get list from WP post table in database
class WB_Getpdflist
{

	public static $pdfs = array();
	
	public static function returnpdflist()
	{

		$query_pdfs_args = array(
		    'post_type' => 'attachment', //any
		    
		    //'post_mime_type' =>'application/pdf', //pfds only
		    //'post_mime_type' =>'application/msword', //documents only
		    //'post_mime_type' => 'none' //everything
		    
		    'post_mime_type' =>'application', 
		    
		    'post_status' => 'inherit', 
		    'posts_per_page' => -1,
		    
		);
			
		try {
			$query_pdfs = new WP_Query( $query_pdfs_args );			
		} catch (Exception $e) {
			exit();
		}
		
		
		foreach ( $query_pdfs->posts as $pdf) {
			$pdfs[]= array(			
					$pdf->guid, 
					$pdf->post_title, 
					$pdf->post_modified_gmt				
				);	   
		}
		
		wp_reset_query();
	
		
		return $pdfs;
		
	}
	
}

//WB_listpdfs-> adds shortcode to list files in a WP HTML page or post
class WB_HTMLlistpdfs 
{

    public function __construct()
    {
        add_shortcode('listpdfs', array($this, 'shortcode_listpdfs_func'));
    }
     
	public function shortcode_listpdfs_func()
	{
		
		$pdflist = new WB_Getpdflist();
		
		foreach ( $pdflist->returnpdflist() as $pdf) {
			echo '<p><a href="' . $pdf[0] . '" target="blank" itemprop="url" alt="' . $pdf[1] . '" >' . $pdf[1] . '</a></p>';	
		}
	
	}
}
$WB_HTMLlistpdfs = new WB_HTMLlistpdfs();



//WPXMLlistpdf-> list applications as an XML page
class WB_XMLlistpdf
{

    public function __construct()
    {        
	add_action( 'template_redirect', array($this, 'XMLPage_listpdfs_func'));
    }
     
	public function XMLPage_listpdfs_func()
	{
	if ( ! preg_match( '/pdf-sitemap\.xml$/', $_SERVER['REQUEST_URI'] ) ) {
	    return;
	  }

	  header( "HTTP/1.1 200 OK" );
	  header( 'X-Robots-Tag: noindex, follow', true );
	  header( 'Content-Type: text/xml' );
	  echo '<?xml version="1.0" encoding="' . get_bloginfo( 'charset' ) . '"?>' . "\n";
	  echo '<!-- generator="' . home_url( '/' ) . '" -->' . "\n";
	  $xml  = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";
	  
	  $pdflist = new WB_Getpdflist();
		
	 foreach ( $pdflist->returnpdflist() as $pdf) {

	    if ( ! empty( $pdf[0] ) ) {
	      $xml .= "\t<url>\n";
	      $xml .= "\t\t<loc>" . $pdf[0] . "</loc>\n";
	      $xml .= "\t\t<lastmod>" . mysql2date( 'Y-m-d\TH:i:s+00:00', $pdf[2], false ) . "</lastmod>\n";
	      $xml .= "\t\t<changefreq>" . 'weekly' . "</changefreq>\n";
	      $xml .= "\t\t<priority>" . '0.7' . "</priority>\n";
	      $xml .= "\t</url>\n";
	    }
	    
	  }
	  
	  $xml .= '</urlset>';
	  echo ( "$xml" );
	  exit();	
				
		
	}
}
 
$WB_XMLlistpdf = new WB_XMLlistpdf();




?>
