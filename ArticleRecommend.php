<?php
    /**
     * ArticleRecommend extension
     *
     * @file
     * @ingroup Extensions
     * @copyright 2012-2013 wiki-learnia.org
     * @license 
     */

    /* Configuration */
    
    // maxmimum amount of articles shown as result
    // default: 5
    $wgArticleRecommendMaxNumber = 5;
    
    // set the default learn target, have to be a category
    // default: defautl
    $wgDefaultUserOptions['ar-learntarget'] = 'default';
    
    // set the default box size
    // defaults are: w = 210 and h = 170;
    $wgArticleRecommendBoxWidth = 210;
    $wgArticleRecommendBoxHeight = 170;
    
    // translate the sidebar title?
    // false results in 'article recommends' as title
    // default: true
    $wgArticleRecommendTranslatedTitle = true;
    
    // where to place the learn target setting?
    // default: misc
    $wgArticleRecommendSettingsSection = 'misc';
    
    
    /* Setup */

    $wgExtensionCredits['other'][] = array(
        'path' => __FILE__,
        'name' => 'ArticleRecommend',
        'author' => 'Felix Beuster',
        'version' => '0.9',
        'url' => 'http://wiki-learnia.org',
        'descriptionmsg' => 'ar-extDescr',
    );
    
    $dir = dirname(__FILE__).'/';
    
    // Localisation
    $wgExtensionMessagesFiles['ArticleRecommend'] = $dir . 'ArticleRecommend.i18n.php';

    // adding a module, for JavaScripts and CSS
    $wgResourceModules['ext.articleRecommend'] = array(
        'scripts' => 'ext.articleRecommend.js',
        'styles'  => 'ext.articleRecommend.css',
        'localBasePath' => dirname( __FILE__ ).'/modules',
        'remoteExtPath' => 'ArticleRecommend/modules',
        'group' => 'ext.articleRecommend',
    );
    
    // including code
    require_once($dir.'db.php');
    include_once($dir.'ArticleRecommend.filter.php');


    // load classes
    $wgAutoloadClasses['ArticleRecommendMain'] = $dir.'classes/ArticleRecommendMain.php';
    $wgAutoloadClasses['ArticleRecommendParser'] = $dir.'classes/ArticleRecommendParser.php';
    $wgAutoloadClasses['ArticleRecommendOutput'] = $dir.'classes/ArticleRecommendOutput.php';

    // hook integration
    $wgAutoloadClasses['ArticleRecommendHooks'] = $dir.'ArticleRecommend.hooks.php';
    $wgHooks['BeforePageDisplay'][] = 'ArticleRecommendHooks::arBeforePageDisplay';
    $wgHooks['SkinBuildSidebar'][] = 'ArticleRecommendHooks::arSkinBuildSidebar';
    $wgHooks['GetPreferences'][] = 'ArticleRecommendHooks::arGetPreferences';

?>