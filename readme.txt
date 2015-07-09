=== CP Related Posts ===
Contributors: codepeople
Donate link: http://wordpress.dwbooster.com/content-tools/related-posts
Tags: post,posts,page,pages,custom post type,related,terms,manual,tags,tags weight,related posts, related pages, associate page, associate post, posts similarity,similarity,shortcode,admin,image,images,plugin,sidebar,widget,rating,filters
Requires at least: 3.0.5
Tested up to: 4.2
Stable tag: 1.0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

CP Related Posts is a plugin that displays related articles on your website, manually, or by the terms in the content, title or abstract, and tags

== Description ==

CP Related Posts is a plugin that displays related articles on your website, manually, or by the terms in the content, title or abstract, including the tags assigned to the articles.

The relationship between posts and pages is determined in a very unique way.  CP Related Posts uses an algorithm that allows you to identify the most representative terms in the content, the title and the abstract of the article, while giving more weight to those terms that match the tags explicitly added to the articles by the author.

In any website, there may be posts that are related, yet with no common terms.  CP Related Posts allows to assign connections to articles manually, attributing the highest level of importance to that type of connection.

CP Related Posts offers a great versatility in displaying related posts. With CP Related Posts, you can define the number of related posts, use a specific layout to display related articles. Similarly, it is possible to decide which information to display for the related post: title, author, tags by which they relate, and importantly, an image that identifies the percentage of correspondence between the two posts.


**CP Related Posts features:**

	> Allows select the number of related posts
	> Allows extract the related terms from the articles titles, contents 
	  and abstracts
	> Uses an automatic algorithm to determine the weight of relationship 
	  between posts
	> Allows associate posts manually (these are the most strong relationships)
	> Allows define a threshold for relations between posts
     (Relations with a weight under the threshold are dismissed)

The base plugin, available for free, from the WordPress Plugin Directory, has all the features needed for associate website's articles.

**Premium features:**

	> Allows to use different layouts for related posts in multi-posts pages
	  (like home page or archives) and for single pages and posts
	> Allows to use different graphics to represent the level of similarity
	> Allows to use related posts with custom post types

**Demo of Premium Version of Plugin**

[http://demos.net-factor.com/related-posts/wp-login.php](http://demos.net-factor.com/related-posts/wp-login.php "Click to access the Administration Area demo")

[http://demos.net-factor.com/related-posts/](http://demos.net-factor.com/related-posts/ "Click to access the Public Page")

If you want more information about this plugin or another one don't doubt to visit my website:

[http://wordpress.dwbooster.com](http://wordpress.dwbooster.com "CodePeople WordPress Repository")

== Installation ==

**To install CP Related Posts, follow these steps:**

1. Download and unzip the plugin
2. Upload the entire "cp-related-posts" directory to the "/wp-content/plugins /" directory
3. Activate the plugin through the 'Plugins' menu in "WordPress"
4. Go to Settings > CP Related Posts and set up your plugin

Another way to install the plugin.

1. Go to the plugins section in your WordPress
2. Press the "Add New" button at beginning of page
3. Search by the plugin name CP Related Posts and install it

== Interface ==

**Setting up CP Related Posts**

The plugin has several setup options to have a great control over related posts. Among the available configuration options you will find:

* Number of related posts
* Post types that admit related posts (the free version of plugi allows only the posts and pages)
* Display percentage of similarity with the symbol... the option allows to select the symbol to represent the similarity between posts
* Display related posts with a percentage of similarity bigger than. Allows to define an acceptable percentage of similarity

Display options for the related posts on the individual post/page

* Display related posts in single pages (checkbox to display or hide the related posts from single pages)
* Display featured images in related posts
* Display percentage of similarity
* Display excerpt of related posts
* Display related terms between related posts
* Display mode (select the layout for related posts)

How to display related posts in multiple-posts pages

* Display related posts in multiple-posts pages
* Display only in homepage
* Exclude the related posts from homepage
* Display the related posts only in specific pages
* Exclude the related posts from specific pages
* Display featured images in related posts
* Display percentage of similarity
* Display excerpt of related posts
* Display related terms between related posts
* Display mode (select the layout for related posts)

Tip: After the initial setup of the plugin, it is recommended to press the button "Process Previous Posts" in order to extract the terms of the posts created before installing the plugin.

On the Editing screen of each post, page, or post type selected in the plugin's settings, you will see a new form that allows you to create direct links between the post being edited and other posts on the website, as well as allowing to extract relevant terms within the text of the post and select them for use as tags of the post in question.

**The way to use CP Related Post from the posts edition**

After type the post's content, press the "Get Recommended Tags" button, to extract all terms (with its weight, form the sections of post). If the user want to increase the importance of specifics terms, only is required check them.

If you want insert relevant terms that are not present in the post's content, define it as a post's tag.

It is possible associate posts manually. Type the terms and press the "Search" button. In the Items found section will be displayed the list of posts/pages that include the term. Select the posts/pages to associate manually.

Note: The posts associated manually represent the most strong relation.

It is possible exclude a post from the list of related posts, with only check the option: "Exclude this post from others related posts", or hide the related posts from a post through the option: "Hide the related posts from this post"

**Filters called by CP Related Posts**

cprp_post_thumbnail: Requires two parameters, a link tag with the thumbnail, and the object with the post's data.

cprp_post_title: Requires two parameters, a div tag with the post title, and the object with the post's data.

cprp_post_percentage: Requires two parameters, a div tag with the percentage of similarity, and the object with the post's data.

cprp_post_excerpt: Requires two parameters, the text of generated excerpt, and the object with the post's data.

cprp_post_tags: Requires two parameters, a div tag with the tags assigned to the post, and the object with the post's data.

cprp_content: Requires two parameters, the html tags of the generated related posts, and an array with the objects of related posts.

== Frequently Asked Questions ==

= Q: How to relate posts manually? =

A: Type the terms and press the "Search" button. In the Items found section will be displayed the list of posts/pages that include the term. Select the posts/pages to associate manually.

= Q: How to break a manual relation? =

A: Go to the post's edition and press the "-" symbol from the items manually related.

= Q: How to use terms that are present in the post's content, title or abstract? =

A: Type the terms as tags associated to the post.

= Q: How to use a different icon to represent the similarity between posts? =

A: You only should replace the images for icon_on and icon_off, located in "/wp-content/plugins/cp-related-posts/images"

= Q: How to remove posts with little similarity? =

A: You only should increase the similarity percentage, from the settings page of plugin.

= Q: How to vary the number of words in the excerpt of related posts? =

A: Go to the settings page of the plugin and enter an integer number in the attributes: "Number of words on posts excerpts" for related posts on single pages, and pages with multiple entries. The integer number represent the maximum amount of words in the excerpts of posts (50 words is the number by default)

= Q: How can be hidden the related posts from the home page? =

A: Go to the settings page of plugin and check the option "Exclude the related posts from homepage"

= Q: How can hide the related posts from a page? = 

A: Go to the page, and check the option "Hide the related posts from this post"

= Q: How can hide some pages of the website from the related posts? = 

A: Go to the page or post and check the option "Exclude this post from others related posts"

== Screenshots ==
01. CP Related Posts with slider layout
02. CP Related Posts with column layout
03. CP Related Posts with accordion layout
04. CP Related Posts with list layout
05. Relating posts
06. Settings page

== Changelog ==

= 1.0 =

* First version released.

= 1.0.1 =

* Improves the plugin documentation.
* Corrects a compatibility issue with the JetPack plugin.
* Includes new features to exclude or include, related posts from specific pages or posts.
* Corrects an issue with pages of multiple entries.
* Improves the selection of related posts and pages.
* Removes some extra tags inserted by other plugins in the excerpts of the posts and pages.
* Allows remove all posts related manually.

= 1.0.2 =

* Corrects an issue with the words to be excluded from the tags, in those languages where are not defined the lists of words.
* Corrects an issue when have not extracted the tags from the posts/pages.

= 1.0.3 =

* Modifies the way that posts are selected manually.

= 1.0.4 =

* Prevents conflicts with other plugins that define the same classes.
* Allows disassociate selected tags from pages.
* Allows to define the similarity as zero.
* Improves some styles applied to the related posts.

= 1.0.5 =

* Removes the shortcodes before extract the tags.
* Includes  new terms in the list of tags to exclude.

= 1.0.6 =

* Displays related posts in the onload event of the "window" object, after the images have been loaded.

= 1.0.7 =

* Reduces the priority of the related posts insertion.
* Inserts a DIV tag with clear:both at the end of related posts.

= 1.0.8 =

* Allows to select the size of images associated to the related posts.
* Allows to enter the number of words to display as excerpts of related posts.

= 1.0.9 =

* Includes filters to allow modify  all sections of the related posts from other plugins, and the active theme on WordPress.

== Upgrade Notice ==

= 1.0.9 =
* Includes filters to allow modify  all sections of the related posts from other plugins, and the active theme on WordPress.

Important note: If you are using the Professional version don't update via the WP dashboard but using your personal update link. Contact us if you need further information: http://wordpress.dwbooster.com/support

= 1.0.8 =
* Allows to select the size of images associated to the related posts.
* Allows to enter the number of words to display as excerpts of related posts.

= 1.0.7 =
* Reduces the priority of the related posts insertion.
* Inserts a DIV tag with clear:both at the end of related posts.

= 1.0.6 =
* Improved plugin features

= 1.0.5 =
* Improved plugin features

= 1.0.4 =
* Improved plugin features

= 1.0.3 =
* Improved plugin features

= 1.0.2 =
* Improved plugin features