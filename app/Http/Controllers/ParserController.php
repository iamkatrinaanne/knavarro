<?php

namespace App\Http\Controllers;
//namespace AppBundle\Controllers;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Smalot\PdfParser\Parser;

/** the PDF Parser class*/



class ParserController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function parseFile($filepath)
    {
        $pdfFilePath = "../public".$filepath;

        $PDFParser = new Parser();

        $pdf = $PDFParser->parseFile($pdfFilePath);

        $text = $pdf->getText();

        return $text;
    }

    public function uploadPDF(Request $request){

        $file = $request->file('scanpdf');
        $new_name = rand().'.'.$file->getClientOriginalExtension(); 
        $file->move(public_path("resources"),$new_name);

        $filepath="/resources/".$new_name;
        
        echo $this->parseFile($filepath);
        
        // return view("revcenter.newquestionsuccess")->with('filepath',$filepath)
        //                                            ->with('filename',$new_name)
        //                                            ->with('lesson_ID',$request->lesson_ID);
        }
}
