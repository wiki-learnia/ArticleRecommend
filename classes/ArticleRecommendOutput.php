<?php

    class ArticleRecommendOutput {
    
        private $maxNumber;
        private $output;
        private $width;
        private $height;
    
        /*** PUBLIC ***/
        
        /**
         * constructor
         */
        public function __construct() {
            global $wgArticleRecommendMaxNumber;
            $this->maxNumber = $wgArticleRecommendMaxNumber;
        }
        
        /**
         * generating the output HTML
         */
        public function run () {
            global $wgOut;
            
            // either calc a recommend or give a random page
            //if($wgOut->getOutput()->isArticle() == 1 && $wgOut->getUser()->isLoggedIn() == 1) {   // deactivated for now to show 5 recommends on every page
            if($wgOut->getUser()->isLoggedIn() == 1) {
                $recArticle = new ArticleRecommendMain($wgOut->getSkin(), $this->maxNumber);
                $recArticle->calcRecommend();
                $articles = $recArticle->getArticleTitles();
            } else {
                $rnd = new RandomPage();
                $articles = array($rnd->getRandomTitle());
            }
            
            $this->calcSize();
            
            // parse the articles preview
            $arParser = new ArticleRecommendParser($articles);
            $set = $arParser->getArticles();
            
            // buildung the HTML
            if($this->width >= 170) {
                $this->output = '<div class="articleRecommend arHolder" style="width: '.$this->width.'px;">'."\n";
                $this->output .= '<div class="arRecContainer" style="width: '.(count($set)*($this->width+10)).'px">'."\n";
                $i = 0;
                foreach($set as $a) {
                    $i++;
                    $this->output .= '<div id="arID'.$i.'" class="arRec" style="width: '.$this->width.'px; height: '.$this->height.'px">'."\n";
                    $this->output .= '<h6>'.$a['title'].'</h6>'."\n";
                    $this->output .= $a['text'].' <a class="arReadMore" href="'.$a['url'].'">'.$wgOut->msg('ar-readMore').'</a>'."\n";
                    $this->output .= '</div>'."\n";
                }
                $this->output .= '<br class="arClear">'."\n";
                $this->output .= '</div>'."\n";
                $this->output .= '</div>'."\n";
                $this->output .= '<ul class="arLinks">'."\n";
                for($j = 1; $j <= count($set); $j++) {
                    $this->output .= '<li class="arLink" goto="arID'.$j.'">ar'.$j.'</li>'."\n";
                }
                $this->output .= '</ul>'."\n"; 
            } else {
                $this->output = '<ul class="articleRecommend onlyList">'."\n";
                foreach($set as $a) {
                    $this->output .= '<li>'."\n";
                    $this->output .= '<a href="'.$a['url'].'">'.$a['title'].'</a>'."\n";
                    $this->output .= '</li>'."\n";
                }
                $this->output .= '</ul>'."\n"; 
            }
        }
    
        /*** GET / SET ***/
        
        /**
         * giving the final HTML output
         *
         * @return String
         */
        public function getOutput() {
            return $this->output;
        }
    
        /*** PRIVATE ***/
        
        /**
         * calculating the box size, depending on the current skin
         */
        private function calcSize() {
            global $wgOut, $wgArticleRecommendBoxWidth, $wgArticleRecommendBoxHeight;
            
            if($wgOut->getSkin()->getSkinName() == 'vector') {
                $this->width = 130;
                $this->height = 315;
            } else if($wgOut->getSkin()->getSkinName() == 'monobook') {
                $this->width = 125;
                $this->height = 350;
            } else if($wgOut->getSkin()->getSkinName() == 'modern') {
                $this->width = 170;
                $this->height = 240;
            } else {
                $this->width = $wgArticleRecommendBoxWidth;
                $this->height = $wgArticleRecommendBoxHeight;
            }
        }
    }
?>