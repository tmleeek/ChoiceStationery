<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_seoautolink
 * @version   1.0.14
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_SeoAutolink_Helper_Help extends Mirasvit_MstCore_Helper_Help
{
    protected $_help = array(
        'system' => array(

                //Auto Links
            'autolink_target'                               => 'Select content types where autolinks should be applied',
            'autolink_excluded_tags'                        => 'Do not add links on certain tags.<xmp></xmp>Example:
                                                                    h1
                                                                    h2 ',
            'autolink_skip_links_for_page'                  => 'Exclude individual pages. Can be a full action name or a request path.<xmp></xmp>Example:
                                                                    /accessories*
                                                                    /home-decor
                                                                    catalog_category_view
                                                                    cms_index_index
                                                                    ',
            'autolink_is_enable_links_for_blog'             => 'If AW_Blog extention enabled, automatic internal links can be generated for it\'s blog pages',
            'autolink_links_limit_per_page'                 => 'Maximum amount of automatic links that can be present on a single page. Leave field empty to disable limit.',
            'autolink_target_template_paths'                => 'Enter paths of templates where you would like to add links (one per line). E.g. frontend/base/default/template/catalog/product/view/description.phtml',
            'autolink_skip_recursive'                       => 'Skip links with URL parameter identical to current page URL. For example link with URL "/home-decor/electronics.html" on Electronics category description with URL "http://site.com/home-decor/electronics.html" will be skipped.',
            'autolink_language_without_space_compatibility' => 'Compatibility with languages without space (e.g. Chinese, Thai etc.)',
        ),
    );

    /************************/
}
