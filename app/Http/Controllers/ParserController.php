<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use PhpParser\Node\Stmt\If_;
use Symfony\Component\DomCrawler\Crawler;
use App\Rent;
use App\Option;



class ParserController extends Controller
{
    public function start()
    {
        if (Session::get('parsingcount')>2 ){
            $parsingcount=Session::get('parsingcount');

        }else{
            Session::put('parsingcount', 2);
            $parsingcount=Session::get('parsingcount');
        }

        $listOffers=[];
        $link='http://cyprus-realty.testsite.co.ua/';
        $links=$this->getLinks($link);

        foreach ($links as $singleLink){

            //get number og pages

            $countPages[]=$this->pageCount($singleLink);

            //get offers of first pages

            if (Session::get('parsingcount')==2 ){

                foreach($this->getOffers($singleLink) as $offer){
                    $listOffers[]=$offer;
                }
            }
            //get offers of other pages

            for($i=$parsingcount; $i<$parsingcount+1; $i++){

                $otherLinks=$singleLink.'?page='.$i;

                foreach($this->getOffers($otherLinks) as $offer){
                    $listOffers[]=$offer;
                }
            }
        }

        $parsingcount+=1;
        Session::put('parsingcount', $parsingcount);

        foreach ($listOffers as $offer){
            $this->getOptions($offer);
        }

        return view('/pagescount', ['listOffers'=>$listOffers, 'parsingcount'=>$parsingcount]);
    }



    public function getOptions($link)
    {
        //get info about rent from html page

        $html=file_get_contents($link);
        $crawler=new Crawler($html);


        $loc=$crawler->filterXpath("//ul[@class='list-unstyled']//li[@class='loc']//span");
           foreach ($loc as $node){
               $location=$node->nodeValue;
           }

        $id=$crawler->filterXpath("//ul[@class='list-unstyled']//li[@class='text-big']//div");
        foreach ($id as $node){
            $id=$node->nodeValue;
            $id=strtr($id,["ID "=>""]);
        }

        $rent=$crawler->filterXpath("//ul[@class='list-unstyled']//li[@class='text-big offer ']//span");
        foreach ($rent as $node){
            $rent=$node->nodeValue;
        }

        $status=$crawler->filterXpath("//ul[@class='list-unstyled']//li[@class='text-big offer2']");
        foreach ($status as $node){
            $status=$node->nodeValue;
            $status=trim(strtr($status,["Статус:"=>""]));
        }

        $price=$crawler->filterXpath("//ul[@class='list-unstyled']//li[@class='text-big nowrap main-price-wrap']//span");
        foreach ($price as $node){
            $price=$node->nodeValue;
        }

        $description=trim(strip_tags(implode($crawler->filterXpath("//input[@class='description_sp']")->extract(array('value')))));

        $name=$crawler->filterXpath("//h1[@class='product-header container name_sp']");
        foreach ($name as $node){
            $name=$node->nodeValue;
        }

        $weblink=$link;


        $rows = array();
        $trElements = $crawler->filterXpath("//table[@class='table']//tbody//tr");
        // iterate over filter results
        foreach ($trElements as $i => $content) {
            $tds = array();
            // create crawler instance for result
            $crawler = new Crawler($content);
            //iterate again
            foreach ($crawler->filter('td') as $i => $node) {
                // extract the value
                $tds[] = $node->nodeValue;
            }
            $rows[] = $tds;
        }

        foreach ($rows as $j=>$item){
            $options[$item[0]]=trim($item[2]);
        }

         $addOptions=explode("  • ", array_pop($options));

        foreach ($options as $value=>$option){

            $newOptions[]=trim($value.$option);
        }
        $rentOptions=array_merge($newOptions, $addOptions);

        //insert info into database

        $rentsId=Rent::all()->pluck('site_id');

        //check if rent already exist and create if not
        $matchId=null;

        foreach ($rentsId as $singleId){
            if ($singleId==$id){
               $matchId=true;
            }
        }

        if ($matchId==null){
            $newRent=Rent::create([
                'location'=>$location,
                'site_id'=>$id,
                'rent_type'=>$rent,
                'status'=>$status,
                'price'=>$price,
                'description'=>$description,
                'name'=>$name,
                'web_link'=>$weblink
            ]);

            $getOptionsName=Option::all()->pluck('name');


            //check if options already exist and create if not
            foreach ($rentOptions as $name){
                $match=null;
                if ($getOptionsName!==null){
                    foreach ($getOptionsName  as $getName){
                        if ($getName==$name){
                            $match=true;
                        }
                    }
                }
                if ($match==null){
                    Option::create(['name'=>$name]);
                }
            }
            //attached options to rent

            foreach ($rentOptions as $name){
                $optionId=Option::where('name', $name)->pluck('id');

                $newRent->options()->attach($optionId);
            }
        }

       /* return view('/result',[
            'options'=>$rentOptions,
            'location'=>$location,
            'id'=>$id,
            'rent'=>$rent,
            'status'=>$status,
            'price'=>$price,
            'description'=>$description,
            'name'=>$name,
            'weblink'=>$weblink
        ]);*/
    }


    public function getLinks($url)
    {
        $links=[];

        $html=file_get_contents($url);
           $crawler=new Crawler($html);

           $hrefs=$crawler->filterXPath("//ul[@class='nav navbar-nav topmenu']//li//a[@class='clippedtext']")->extract(array('onclick'));

           foreach ($hrefs as $link){
               $link=strtr($link,["location='"=>"", "';"=>""]);
               $links[]=$link;
           }
         return  $links;
        }

//get number of last page

    public function pageCount($link)
    {
        $html=file_get_contents($link);
            $crawler=new Crawler($html);

            $pages=$crawler->filterXPath("//div[@class='pagination row hidden-print']//div[@class='text-right']//p");
        foreach ($pages as $node){
            $pages=$node->nodeValue;
        }

        $position=mb_strrpos($pages, ")");
        $count=trim(mb_substr($pages, $position-3, 3));

            return $count;
    }

//get tentsOffers from page

    public function getOffers($link)
    {
        $offers=[];

        $html=file_get_contents($link);
        $crawler=new Crawler($html);
        $offersPerPage=$crawler->filterXPath("//a[@class='gbtn pull-right']")->extract(array('href'));

        foreach($offersPerPage as $offer){
            $offers[]=$offer;
        }
        return $offers;
    }

}
