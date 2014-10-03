<?php
    /**
     * ArticleRecommend extension
     *
     * @file
     * @ingroup Extensions
     * @copyright 2012-2013 wiki-learnia.org
     * @license 
     */

class ArticleRecommendHooks {

    /**
     * Adding our moudle for CSS and JS
     * and updating the user's history
     *
     * This is attached to the BeforePageDisplay hook
     *
     * @param $out OutputPage
     * @param $skin Skin
     */
    public static function arBeforePageDisplay( &$out, &$skin ) {
        // load modules
        $out->addModules( 'ext.articleRecommend' );
        return true;
    }

    /**
     * Adding our recommendation slider to the sidebar
     *
     * This is attached to the SkinBuildSidebar hook
     *
     * @param $skin Skin
     * @param $bar
     */
    public static function arSkinBuildSidebar( $skin, &$bar ) {
        global $wgOut, $wgArticleRecommendTranslatedTitle;
        $output = new ArticleRecommendOutput();
        $output->run();
        
        if($wgArticleRecommendTranslatedTitle) {
            $sidebarTitle = $wgOut->msg('ar-sidebarTitle').'';
        } else {
            $sidebarTitle = 'article recommends';
        }
        
        $bar[ $sidebarTitle ] = $output->getOutput();
        return true;
    }

    /**
     * Adding options for a learn target and history
     *
     * This is attached to the GetPreferences hook
     *
     * @param $user User
     * @param $preferences
     */
    public static function arGetpreferences( $user, &$preferences ) {
        global $wgHiddenPrefs, $wgArticleRecommendSettingsSection, $wgOut;
        $categories = ar_getAllCategories();
        $selects = array();
        $selects[$wgOut->msg('ar-prefSelectTitle').''] = 'default';
        foreach($categories as $cat) {
            $selects[ar_getCategoryName($cat)] = $cat->cl_to;
        }
        
		$preferences['ar-learntarget'] = array(
			'type' => 'select',
			'label-message' => 'ar-preflearntarget',
			'section' => $wgArticleRecommendSettingsSection,
            'options' => $selects,
            'default' => $user->getOption('ar-learntarget'),  // A 'default' key is required!
		);
		return true;
    }
}

?>
