<?php

class ArticleRecommendParser {
    
    private $articles;      // array of parsed articles
                            // array(
                            //      title   ->  (string) the articles title
                            //      text    ->  (string) the articles text
                            //      url     ->  (string) the link for the article
                            // )
    private $articleTitles; // array of article titles
    
    /*** public ***/
    
    public function __construct($titles) {
        $this->setArticleTitles($titles);
        $this->parseArticles();
        
    }
    
    /*** get / set ***/
    
    public function getArticleTitles() {
        return $this->articleTitles;
    }
    
    public function setArticleTitles($titles) {
        $this->articleTitles = $titles;
    }
    
    public function getArticles() {
        return $this->articles;
    }
    
    public function setArticles($articles) {
        $this->articles = $articles;
    }
    
    /*** private ***/
    
    private function parseArticles() {
        global $wgOut;
        foreach($this->articleTitles as $key => $title) {
            // get the article
            $art = Article::newFromTitle($title, $wgOut->getContext());
            
            // shorten the text
            $this->currentText = $art->getPage()->getRawText();
            $this->convert();
            
            // options for the parser
            $opt = ParserOptions::newFromUser($wgOut->getUser());
            
            // setup parser
            $parser = new Parser();
            
            // parse the text
            $res = $parser->parse($this->currentText, $title, $opt);
            
            $this->articles[$key] = array(
                                        'title' => $title->getText(),
                                        'text' => $res->getText(),
                                        'url' => $title->getFullUrl());
        }
    }
    
    private function convert() {
        $this->removeQrCode();
        $this->removeHeadlines();
        $this->removeBoxes();
        $this->removeRefs();
        $this->removeMedia();
        $this->shorten(200, '...');
        $this->currentText = $this->currentText."\n__NOEDITSECTION__ __NOTOC__ __NOGALLERY__";
    }

    private function removeQrCode() {
        $this->currentText = preg_replace('+{{(#qrcode:)(.*)*?}}+', '', $this->currentText);
    }
    
    private function removeHeadlines() {
        for($i = 6; $i >= 1; --$i) {
            $h = str_repeat( '=', $i );
            $this->currentText = preg_replace( "/^$h(.+)$h\\s*$\n/m", "", $this->currentText);
        }
    }
    
    private function removeBoxes() {
        $this->currentText = preg_replace('/\{[^\}]*\}}/', '', $this->currentText);
    }
    
    private function removeRefs() {
        $this->currentText = preg_replace('=<ref(.*?)>(.*?)</ref> =', '', $this->currentText);
    }
    
    private function removeMedia() {
        $this->currentText = preg_replace('/\[[^\]]*\]]/', '', $this->currentText);
    }

    private function shorten($limit, $link='') {
    
        $text = $this->currentText;
        
        // do nothing if text is shorter then limit
        if ($limit >= strlen($this->currentText))
            return;
         
        // check without html comments
        $this->currentText = preg_replace('/<!--.*?-->/s', '', $this->currentText);
        if ($limit >= strlen($this->currentText))
            return;
 
        // search latest position with balanced brackets/braces
        // store also the position of the last preceding space
                 
        $brackets = 0;
        $cbrackets = 0;
        $n0 = -1;
        $nb = 0;
        for ($i=0; $i < $limit; $i++) {
            $c = $this->currentText[$i];
            if ($c == '[') $brackets++;
            if ($c == ']') $brackets--;
            if ($c == '{') $cbrackets++;
            if ($c == '}') $cbrackets--;
            // we store the position if it is valid in terms of parentheses balancing
            if ($brackets == 0 && $cbrackets == 0) {
                $n0 = $i;
                if ($c == ' ') $nb = $i;
            }
        }
 
        // if there is a valid cut-off point we use it; it will be the largest one which is not above the limit 
        if ( $n0 >= 0 )  {
            // we try to cut off at a word boundary, this may lead to a shortening of max. 15 chars
            if ($nb > 0 && $nb + 15 > $n0)
                $n0 = $nb;
            $cut = substr($this->currentText, 0, $n0+1);
                         
            // an open html comment would be fatal, but this should not happen as we already have 
            // eliminated html comments at the beginning
 
            // some tags are critical: ref, pre, nowiki
            // if these tags were not balanced they would spoil the result completely
            // we enforce balance by appending the necessary amount of corresponding closing tags
            // currently we ignore the nesting, i.e. all closing tags are appended at the end.
            // This simple approach may fail in some cases ...
                         
            $matches = array();
            $noMatches = preg_match_all('#<\s*(/?ref|/?pre|/?nowiki)(\s+[^>]*?)*>#im', $cut, $matches);
            $tags = array ('ref' => 0, 'pre' => 0, 'nowiki' => 0);
                         
            if ($noMatches>0) {
                // calculate tag count (ignoring nesting)
                foreach ($matches[1] as $mm) {
                    if ($mm[0] == '/')
                        $tags[substr($mm,1)] --;
                    else
                        $tags[$mm]++;
                }
                // append missing closing tags - should the tags be ordered by precedence ?
                foreach($tags as $tagName => $level) {
                    while ($level>0) {
                        // avoid empty ref tag
                        if ($tagName == 'ref' && substr($cut, strlen($cut) - 5) == '<ref>') {
                            $cut=substr($cut, 0, strlen($cut)-5);
                        } else {
                            $cut.='</'.$tagName.'>';
                        }
                        $level--;
                    }
                }
            }
            $this->currentText = $cut.$link;
        } else if ($limit == 0) {
            $this->currentText = $link;
        } else {
            // otherwise we recurse and try again with twice the limit size; this will lead to bigger output but
            // it will at least produce some output at all; otherwise the reader might think that there
            // is no information at all
            $this->currentText = $this->shorten($limit * 2, $link);
        }
    }
}

?>