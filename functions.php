<?php

//Child Theme Functions File
add_action("wp_enqueue_scripts", "enqueue_wp_child_theme");
function enqueue_wp_child_theme()
{
    //This is your parent stylesheet you can choose to include or exclude this by going to your Child Theme Settings under the "Settings" in your WP Dashboard
    wp_enqueue_style("parent-css", get_template_directory_uri() . "/style.css");

    //This is your child theme stylesheet = style.css
    wp_enqueue_style("child-css", get_stylesheet_uri());
}


/**
 * Prints HTML with meta information for current post: categories, tags, permalink, author, and date.
 * Create your own kleo_entry_meta() to override in a child theme.
 * @since 1.0
 */
function kleo_entry_meta($echo = true, $att = array())
{
    global $kleo_config;
    $meta_list     = [];
    $author_links  = '';
    $meta_elements = sq_option('blog_meta_elements', $kleo_config['blog_meta_defaults']);

    // Translators: used between list items, there is a space after the comma.
    if (in_array('categories', $meta_elements)) {
        $categories_list = get_the_category_list(esc_html(_x(', ', 'Categories separator', 'kleo')));
    }

    // Translators: used between list items, there is a space after the comma.
    if (in_array('tags', $meta_elements)) {
        $tag_list = get_the_tag_list('', esc_html(_x(', ', 'Tags separator', 'kleo')));
    }

    $date = sprintf(
        '<a href="%1$s" rel="bookmark" class="post-time">' .
        '<time class="entry-date" datetime="%2$s">%3$s</time>' .
        '<time class="modify-date hide hidden updated" datetime="%4$s">%5$s</time>' .
        '</a>',
        esc_url(get_permalink()),
        esc_attr(get_the_date('c')),
        esc_html(get_the_date()),
        esc_html(get_the_modified_date('c')),
        esc_html(get_the_modified_date())
    );

    if (is_array($meta_elements) && !empty($meta_elements)) {
        if (in_array('author_link', $meta_elements) || in_array('avatar', $meta_elements)) {
            $authors = get_multiple_authors();

            if (!empty($authors)) {
                foreach ($authors as $author) {
                    /* If buddypress is active then create a link to Buddypress profile instead */
                    if (function_exists('bp_is_active')) {
                        $author_link  = esc_url(
                            bp_core_get_userlink($author->user_id, $no_anchor = false, $just_link = true)
                        );
                        $author_title = esc_attr(
                            sprintf(esc_html__('View %s\'s profile', 'kleo'), $author->display_name)
                        );
                    } else {
                        $author_link  = esc_url($author->link);
                        $author_title = esc_attr(
                            sprintf(esc_html__('View all POSTS by %s', 'kleo'), $author->display_name)
                        );
                    }

                    $author_markup = sprintf(
                        '<a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s %4$s</a>',
                        $author_link,
                        $author_title,
                        in_array('avatar', $meta_elements) ? $author->get_avatar(50) : '',
                        in_array(
                            'author_link',
                            $meta_elements
                        ) ? '<span class="author-name">' . $author->display_name . '</span>' : ''
                    );

                    $meta_list[] = '<small class="meta-author author vcard">' . $author_markup . '</small>';
                }
            }
        }

        if (function_exists('bp_is_active')) {
            if (in_array('profile', $meta_elements)) {
                $author_links .= '<a href="' . bp_core_get_userlink(
                        get_the_author_meta('ID'),
                        $no_anchor = false,
                        $just_link = true
                    ) . '">' .
                    '<i class="icon-user-1 hover-tip" ' .
                    'data-original-title="' . esc_attr(
                        sprintf(esc_html__('View profile', 'kleo'), get_the_author())
                    ) . '"' .
                    'data-toggle="tooltip"' .
                    'data-placement="top"></i>' .
                    '</a>';
            }

            if (bp_is_active('messages') && is_user_logged_in()) {
                if (in_array('message', $meta_elements)) {
                    $author_links .= '<a href="' . wp_nonce_url(
                            bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?r=' . bp_core_get_username(
                                get_the_author_meta('ID')
                            )
                        ) . '">' .
                        '<i class="icon-mail hover-tip" ' .
                        'data-original-title="' . esc_attr(
                            sprintf(esc_html__('Contact %s', 'kleo'), get_the_author())
                        ) . '" ' .
                        'data-toggle="tooltip" ' .
                        'data-placement="top"></i>' .
                        '</a>';
                }
            }
        }

        if (in_array('archive', $meta_elements)) {
            $author_links .= '<a href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' .
                '<i class="icon-docs hover-tip" ' .
                'data-original-title="' . esc_attr(
                    sprintf(esc_html__('View all posts by %s', 'kleo'), get_the_author())
                ) . '" ' .
                'data-toggle="tooltip" ' .
                'data-placement="top"></i>' .
                '</a>';
        }
    }

    if ('' != $author_links) {
        $meta_list[] = '<small class="meta-links">' . $author_links . '</small>';
    }

    if (in_array('date', $meta_elements)) {
        $meta_list[] = '<small>' . $date . '</small>';
    }

    $cat_tag = array();

    if (isset($categories_list) && $categories_list) {
        $cat_tag[] = $categories_list;
    }

    if (isset($tag_list) && $tag_list) {
        $cat_tag[] = $tag_list;
    }
    if (!empty($cat_tag)) {
        $meta_list[] = '<small class="meta-category">' . implode(', ', $cat_tag) . '</small>';
    }

    //comments
    if ((!isset($att['comments']) || (isset($att['comments']) && false != $att['comments'])) && in_array(
            'comments',
            $meta_elements
        )) {
        $meta_list[] = '<small class="meta-comment-count"><a href="' . get_permalink(
            ) . '#comments">' . get_comments_number() .
            ' <i class="icon-chat-1 hover-tip" ' .
            'data-original-title="' . esc_attr(
                sprintf(
                    _n('This article has one comment', 'This article has %1$s comments', get_comments_number(), 'kleo'),
                    number_format_i18n(get_comments_number())
                )
            ) . '" ' .
            'data-toggle="tooltip" ' .
            'data-placement="top"></i>' .
            '</a></small>';
    }

    $meta_separator = isset($att['separator']) ? $att['separator'] : sq_option('blog_meta_sep', ', ');

    if ($echo) {
        echo implode($meta_separator, $meta_list);
    } else {
        return implode($meta_separator, $meta_list);
    }
}
