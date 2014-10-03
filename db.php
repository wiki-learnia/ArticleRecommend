<?php    
    /**
     * get all categories from database
     * 
     * @return $list
     */
    function ar_getAllCategories() {    
        $db = & wfGetDB( DB_SLAVE );
        $res = $db->select(
            array('categorylinks'),
            array('cl_to'),
            array(),
            '',
            array('GROUP BY' => 'cl_to', 'ORDER BY' => 'cl_to ASC'));
        if($res !== false) {
            $list = array();
            while ($x = $db->fetchObject($res)) {
                if( !ar_hasNamespace($x->cl_to) &&
                    !ar_isOnLearntargetBlacklist($x->cl_to)) {
                    $list[] = $x;
                }
            }
            $db->freeResult($res);
        } else {
        }
        return $list;
    }

    /**
     * checks whether the name has a namespace in it or not
     * 
     * @return boolean
     */
    function ar_hasNamespace($name) {
        if(preg_match('![\s#:|]+!', $name))
            return true;
        return false;
    }

    /**
     * checks wheter the name is on the learntarget blacklist or not
     * 
     * @return boolean
     */
    function ar_isOnLearntargetBlacklist($name) {
        global $arLearntargetBlacklist;
        if(in_array($name, $arLearntargetBlacklist))
            return true;
        return false;
    }
    
    /**
     * extract the name of a category
     * 
     * @return $title
     */
    function ar_getCategoryName($category) {
        $title = Title::makeTitle(NS_CATEGORY, $category->cl_to);
        return $title->mTextform;
    }
    
    /**
     * extract the names of a category list
     * 
     * @return $list
     */
    function ar_getCategoryNames($categories) {
        $list = array();
        foreach($categories as $cat) {
            $title = Title::makeTitle(NS_CATEGORY, $cat->cl_to);
            $list[] = $title->mTextform;
        }
        return $list;
    }
    
    /**
     * request the scores from ArticleFeedback extension
     *
     * @param $title Title the articles title
     *
     * @return float
     */
    function ar_getArticleFeedbackScore($title) {
        $value = 0;
        $db = & wfGetDB( DB_SLAVE );
        $res = $db->select(
            array('article_feedback_pages'),
            array('aap_rating_id', 'aap_total', 'aap_count'),
            'aap_page_id = '.$title->getArticleID());
        if($res !== false) {
            $list = array();
            while ($x = $db->fetchObject($res))
                $list[] = $x;
            $db->freeResult($res);
            foreach($list as $listEntry) {
		if (($listEntry->aap_count)==0){
                    $value += 0;
		}
		else{
                    $value += $listEntry->aap_total / $listEntry->aap_count;
		}
            }
            $value /= 4;
        }
        if($value == 0) {
            // medium value, if something went wrong
            $value = 3;
        }
        
        // 1 <= $value <= 5
        return $value;
    }
?>
