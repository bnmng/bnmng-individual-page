=== Individual Page ===
Contributors: bnmng
Tags: post content
Donate link: http://bnmng.com/donations/
Requires at least: 4.0
Tested up to: 5.0
Requires PHP: 7.0
Stable tag: 1.1
License: GPLv2 or later

Opens a post in a new window, as a separate page without Wordpress features, with optional custom formatting.

== Description ==

This plugin creates a shortcode which can be placed in a post or a page.  The shortcode creates a link to open the post in a new window - aka an independent page - which contains just the post title and content with some optional modification and styling.

The default structure of the independent page is:
&lt;html&gt;
	&lt;head&gt;
		&lt;title&gt;&lt;!-- the title of the post --&gt;&lt;/title&gt;
	&lt;/head&gt;
	&lt;body&gt;
		&lt;article&gt;
			&lt;header&gt;
				&lt;h1&gt;&lt;!-- the title of the post --&gt;&lt;/h1&gt;
			&lt;/header&gt;
			&lt;div id="content"&gt;
				&lt;!-- the content of the post --&gt;
			&lt;/div&gt;
		&lt;/article&gt;
	&lt;/body&gt;
&lt;/html&gt;

where &lt;!-- the title of the post --&gt; and &lt;!-- the content of the post --&gt; are the actual title and content.  Additionally, the following can be added:

1) This can be added in the &lt;head&gt;:
&lt;link rel="stylesheet" href="&lt;!-- url of your external stylesheet --&gt;"&gt;

2) This can be added in the &lt;head&gt;:
&lt;style type="text/css"&gt;
&lt;!-- your custom styling --&gt;
&lt;/style&gt;

3) This can be added after the opening &lt;body&gt; tag:
&lt;header&gt;
&lt;!-- your custom page header --&gt;
&lt;/header&gt;


4) This can be added before the closing &lt;/body&gt; tag:
&lt;footer&gt;
&lt;!-- your custom page footer --&gt;
&lt;/footer&gt;

The shortcode is &#91;individual_page&#93;.  It will work without any attributes to create an unstyled version of the post.  See the FAQ below for adding attributes.

== Frequently Asked Questions ==

= How is the styling added? =

Styling can be added either in the shortcode ( &#91;individual_page css_url="http://example.com/style.css" css_head="body: { color:white; background-color:blue; }" &#93; ) or by creating a style set in the options.  Each style set has a name which can be refered to in the shortcode ( &#91;individual_page set="style1" &#93; )

== Screenshots ==

1. Settings for an instance
2. Adding a custom post type

== Changelog ==
1.0
Initial Submission

