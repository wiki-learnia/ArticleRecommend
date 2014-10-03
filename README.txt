1. Overview
    As you read through a wiki, you see many different articles. Good ones and bad ones, some interest your
    more then others. ArticleRecommend shows you up to 5 articles as a recommendation. Based on your history
    and the articles feedback. This basic algorithm can be edited and extended as you want it, but for this
    you have to edit the source.
    
    ArticleRecommend adds a box to your wikis sidebar and with the help of JavaScript the user is able to
    scroll through the recommendations. If the available space is not big enough (the width of the sidebar
    is to low) the extension will just show a simple list instead of an animated box.
    The normal user can set a learn target. This is a category, from that the user want to read articles and
    learn. For the recommendation, only articles from this category (and all below) are used.
    Recommendations are only calculated for logged in users on article pages. On special pages and for anon
    users, the extension shows a random article.
    
    Note:
    This extensions is developed as part of a specif skin for a web project, so it is optimized in that case.
    But of course you can use it in any wiki, there should be no problems. Some more information are listed
    in the section 'Configuration' below.
    
2. Depencies
    This extensions needs two other extension to work correctly. Install and use them as required, and this
    extension should work correctly.
        -   ArticleFeedback
        -   UserHistory

3. Installation
    To install ArticleRecommend just move all contents of the archive in a subfolder of your extension folder
    in your mediawiki installation directory. Name this folder 'ArticleRecommend' and your half done.
    In your LocalSettings.php add the following line:

    require_once("$IP/extensions/ArticleRecommend/ArticleRecommend.php");

    Now your done and you can use the extension, or configure it as shown below.
    
4. Configuration
    Out of the box, this extension works correctly and no configuration is needed. But if you want, the
    following variables can be overwritten in LocalSettings.php:
    
    $wgArticleRecommendMaxNumber
        Set this to specify how many articles should be shown in the recommend box. This should be an integer
        value an is a maximum value. So the final number of recommendations can be lower.
        Use like this example (which is the default value):

        $wgArticleRecommendMaxNumber = 5;


    $wgDefaultUserOptions['ar-learntarget']
        Here you can set a default learn target, which is used for recommendations. The value should be the title
        of an existing category.
        Use like this example (which is the default value):

        $wgDefaultUserOptions['ar-learntarget'] = 'default';


    $wgArticleRecommendBoxWidth, $wgArticleRecommendBoxHeight
        This extension was developed in an specific skin, with a wider sidebar. This is why the default size is
        that 'huge'. There is a support for the skins vector, monobook and modern. But in case the width is not
        perfect, set this values. This are ABSOLUTE PIXEL values.
        Use like this example (which are the default values):
        
        $wgArticleRecommendBoxWidth = 210;
        $wgArticleRecommendBoxHeight = 170;


    $wgArticleRecommendTranslatedTitle
        This extension was developed in an specific skin, with a manual sidebar. To pick this module up, the
        title can be fixed and protected from translation. But in most cases you want a translation, for that
        case you should leave this setting in default 'true'.
        Use like this example (which is the default value):
    
        $wgArticleRecommendTranslatedTitle = true;


    $wgArticleRecommendSettingsSection
        By default ArticleRecommend settings are placed in the misc area in settings menu. If you want to place
        it somewhere else, edit this.
        Use like this example (which is the default value):
        
        $wgArticleRecommendSettingsSection = 'misc';
