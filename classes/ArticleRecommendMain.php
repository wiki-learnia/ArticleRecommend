<?php
    class ArticleRecommendMain {
        private $currentArticle     = '';   // Title object
        private $user               = '';
        private $skin               = '';
        private $maximum            = 5;
        
        private $learnTarget        = '';   // Title object
        
        private $articleTitles      = array();
        private $articleScoreTitles = array();
        
        private $articleCheckAll       = array();
        private $articleCheckRecommend = array();
        
        /**
         * constructor
         *
         * @param $skin Skin
         * @param $max int number of recommended articles
         */
        public function __construct($skin, $max) {
            $this->currentArticle = $skin->getTitle();
            $this->user           = $skin->getUser();
            $this->maximum        = $max;
            $this->skin           = $skin;            
            $this->learnTarget    = $this->getUserLearnTarget();
        }
        
        /*** PUBLIC ***/
        
        /**
         * main procedur for generating an recommend (or more)
         */
        public function calcRecommend() {
            $this->recommendAlgorithm();
        }
        
        /*** GET /SET ***/
        
        /**
         * access for the result
         * 
         * @return array(Title) the article titles
         */
        public function getArticleTitles() {
            return $this->articleTitles;
        }
        
        /**
         * list of all articles (title text)
         *
         * @return array(String)
         */
        public function getArticleCheckAll() {
            return $this->articleCheckAll;
        }
        
        /**
         * list of recommended articles (title text)
         *
         * @return array(String)
         */
        public function getArticleCheckRecommend() {
            return $this->articleCheckRecommend;
        }
        
        /*** PRIVATE ***/
        
        /**
         * recommend algorithm
         */
        private function recommendAlgorithm() {
            // have a valid category?
            if($this->isLearnTargetCategory()) {
                // collecting all articles from the set category
                $this->articleTitles = $this->getCategoryArticles($this->learnTarget);
                
                // make a list with just the text of all titles
                foreach($this->articleTitles as $title) {
                    $this->articleCheckAll[] = $title->getText();
                }
                
                $this->removeCurrentArticle();
                $this->removeNamespaceBlacklist();
                $this->hasUserReadArticle();
                #$this->matchingTags();
                #$this->checkLearnPath();
                $this->checkRanking();
                
                // make a list with just the text of recommend titles
                foreach($this->articleTitles as $title) {
                    if(!ar_hasNamespace($title->getText())) {
                        $this->articleCheckRecommend[] = $title->getText();
                    }
                }
            }
            // reducing array, or add a random one to fill up
            $this->sizeArray();
        }
        
        /**
         * remove current articles from array
         */
        private function removeCurrentArticle() {
            foreach($this->articleTitles as $key => $title) {
                if($title->getText() == $this->currentArticle->getText()) {
                    unset($this->articleTitles[$key]);
                    break;
                }
            }
        }
        
        /**
         * remove current articles from array
         */
        private function removeNamespaceBlacklist() {
            global $arNamespaceBlacklist;
            foreach($this->articleTitles as $key => $title) {
                if(in_array($title->getNamespace(), $arNamespaceBlacklist)) {
                    unset($this->articleTitles[$key]);
                }
            }
        }
        
        /**
         * chekcs wether a user read an article or not
         * if yes, unset from array
         */
        private function hasUserReadArticle() {
            $history = $this->getReadArticles();
            
            foreach($this->articleTitles as $key => $title) {
                if(in_array($title->getDBkey(), $history)) {
                    unset($this->articleTitles[$key]);
                }
            }
        }
        
        /**
         * checks wether two articles match in some tags or not
         * if not, unset from array
         */
        private function matchingTags() {
            $tagsCurrent = $this->getArticleTags($this->currentArticle);
            
            // match each article
            foreach($this->articleTitles as $key => $article) {
                $matches = 0;
                $tagsThisArticle = $this->getArticleTags($article);
                
                // find matches
                foreach($tagsCurrent as $tag) {
                    if(in_array($tag, $tagsThisArticle)) {
                        $matches++;
                    }
                }
                
                // the article should have 3 matches for a recommend
                if($matches < 3) {
                    unset($this->articleTitles[$key]);
                }
            }
        }
        
        /**
         * checking article and learnPath relations
         * bad result -> unset from array
         */
        private function checkLearnPath() {
            foreach($this->articleTitles as $key => $title) {
                /* in final use it should be
                 if(isInLearnPath($title))
                    true -> unset
                    false -> do nothing */
                if(false) {
                    unset($this->articleTitles[$key]);
                }
            }
        }
        
        /**
         * checking articles user ranking
         * to low values are not good
         */
        private function checkRanking() {
            foreach($this->articleTitles as $key => $title) {
                $score = $this->getArticleScore($title);
                
                $this->articleScoreTitles[$key] = array(
                                                        'title' => $title,
                                                        'score' => $score,
                                                    );
            }
            
            // now sorting the array, higher is better
            usort(
                $this->articleScoreTitles, function($a, $b) {
                    if($a['score'] == $b['score']) {
                        return 0;
                    }
                    return ($a['score'] > $b['score']) ? -1 : 1;
                });
        }
        
        /**
         * check wether there are still articles in the array or not
         * if not, insert a random one
         * if yes, shrink down to maximum
         */
        private function sizeArray() {
            if(empty($this->articleScoreTitles)) {
                // get a random wiki article
                $rnd = new RandomPage();
                $this->articleScoreTitles = array(array('score' => 3, 'title' => $rnd->getRandomTitle()));
            } else {
                $counter = count($this->articleScoreTitles);
                
                while($counter > $this->maximum) {
                    // because we already sorted the array from high to low,
                    // we can easily pop the last element in array
                    array_pop($this->articleScoreTitles);
                    $counter--;
                }
            }
            $this->articleTitles = array();
            foreach($this->articleScoreTitles as $key => $val) {
                $this->articleTitles[] = $val['title'];
            }
        }
        
        /**
         * collect all articles from category array
         *
         * @param $categories Title array
         *
         * @return array(Title)
         */
        private function getAllCategoriesArticles($categories) {
            $articles = array();
            foreach($categories as $category) {
                $cat = Category::newFromTitle($category);
                
                $children = $cat->getMembers();
                if($cat->getPageCount() === 0) {
                    // $category has no children, add it
                    $articles[] = $category;
                } else {
                    // has children
                    $children = $this->nextChildLevel($children);
                    do {
                        foreach($children['children'] as $child) {
                            $articles[] = $child;
                        }
                        $nexts = $children['nexts'];
                        if(count($nexts) === 0)
                            break;
                        $children = $this->nextChildLevel($nexts);
                    } while(count($nexts) !== 0);
                }
            }
            return $articles;
        }
        
        /**
         * get the next level in child search
         *
         * @param $children Title object
         *
         * @return array(
         *          $children Title array, dead ends in search
         *          $nexts Title array, next nodes to search
         *      )
         */
        private function nextChildLevel($children) {
            $childrenR = array();
            $nexts = array();
            foreach($children as $child) {
                $cat = Category::newFromTitle($child);
                $newChildren = $cat->getMembers();
                if($cat->getPageCount() === 0) {
                    $childrenR[] = $child;
                } else {
                    foreach($newChildren as $next) {
                        // some category and article titles may be the same
                        // in this case, getMenbers() of a cat will always
                        // return the category/article itself
                        // this condition should stop the loop
                        if($child->getText() !== $next->getText()) {
                            $nexts[] = $next;
                        } else {
                            $childrenR[] = $next;
                        }
                    }
                }
            }
            return array('children' => $childrenR, 'nexts' => $nexts);
        }
        
        /**
         * collect all articles from a category in one array
         *
         * @param $category Title a single category
         *
         * @return array(Title) the category articles
         */
        private function getCategoryArticles($category) {
            return $this->getAllCategoriesArticles(array($category));
        }
        
        /**
         * get tags of an single article
         *
         * @param Title articles title
         *
         * @return array() the article tags
         */
        private function getArticleTags($title) {
            $tags = array();
            return $tags;
        }
        
        /**
         * get users learntarget
         *
         * @return Title the learn target category
         */
        private function getUserLearnTarget() {            
            return Title::makeTitle(NS_CATEGORY, $this->user->getOption('ar-learntarget'));
        }
        
        /**
         * checks, wether the learn target is a category or not
         *
         * @return boolean
         */
        private function isLearnTargetCategory() {
            $category = Category::newFromTitle($this->learnTarget);
            if($category->getID() === false) {
                return false;
            }
            return true;
        }
        
        /**
         * get articles, wich user has already read
         *
         * @return array(String) the DB-Keys of the articles from history
         */
        private function getReadArticles() {
            return UserHistoryAPI::getHistoryEntries($this->user);
        }
        
        /**
         * get the ranking value of an article
         *
         * @param $title articles title objct
         *
         * @return float the score
         */
        private function getArticleScore($title) {
            return ar_getArticleFeedbackScore($title);
        }
    }
?>