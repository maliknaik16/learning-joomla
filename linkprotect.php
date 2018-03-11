<?php
/**
 * @package 	Joomla.Plugin
 * @subpackage 	Content.joomla
 *
 * @copyright 	Copyright (c) 2018 Malik Naik . All rights reserved
 * @license 	GNU General Public License v2 or later
*/

defined('_JEXEC') or die; // This line of code prevents the direct access of the file.

/**
 * Link Protect Content Plugin
 * 
 * @package 	Joomla.Plugin
 * @subpackage 	Content.joomla
 * @since 		3.0
*/

class PluginContentLinkProtect extends JPlugin
{
	private $callbackFunction;
	// Define the constructor
	public function __construct(&$subject, $config = array())
	{
		// Call the parent constructor(i.e.JPlugin class's constructor)
		parent::__construct($subject,$config);

		require_once __DIR__ . '/helper/helper.php'; // Include the helper.php
		// $params is defined in JPlugin class and it contains all the parameters which were defined in the linkprotect.xml file.
		$helper = new LinkProtectHelper($this->params);
		$this->callbackFunction = array($helper, 'replaceLinks');
	}
	/**
	 * This function Initializes the plugin
	 * @param 	string 	$context 	The context of the content passes to the plugin
	 * @param 	object 	$article 	The article object
	 * @param 	object 	$params 	The parameters of the article(they are different from plugin params)
	 *
	 * @return 	boolean 			True if the function is bypassed Else True/False based on the replacement action
	*/
	public function onContentBeforeDisplay($context, $article, $params) 
	{
		$parts = explode('.',$context);
		if ($parts[0] != 'com_content')
		{
			return;
		}
		// If the user inserts {likprotect=off} in the article content then do not run the plugin on that article
		if(stripos($article->text, '{linkprotect=off}') === true)
		{
			// Remove the {linkprotect=off} from the article
			$article->text = str_replace('{linkprotect=off}', '', $article->text);
		}

		$app = JFactory::getApplication();
		$external = $app->input->get('external', NULL);
		if ($external) 
		{
			LinkProtectHelper::leaveSite($article,$external);
		}else
		{
			$pattern = '@href=("|\')(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*[\?\S+)?)?)?]("|\')@';
			$article->text = preg_replace_callback($pattern, $this->callbackFunction, $article->text);
		}
	}
}
?>