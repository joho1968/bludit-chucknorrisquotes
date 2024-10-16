<?php
/*
 * Chuck Norris Quotes Plugin for Bludit
 *
 * plugin.php (chucknorrisquotes)
 * Copyright 2024 Joaquim Homrighausen; all rights reserved.
 * Development sponsored by WebbPlatsen i Sverige AB, www.webbplatsen.se
 *
 * This file is part of chucknorrisquotes. chucknorrisquotes is free software.
 *
 * chucknorrisquotes is free software: you may redistribute it and/or modify it
 * under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3 as published by
 * the Free Software Foundation.
 *
 * chucknorrisquotes is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU AFFERO GENERAL PUBLIC
 * LICENSE v3 for more details.
 *
 * You should have received a copy of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * along with the chucknorrisquotes package. If not, write to:
 *  The Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor
 *  Boston, MA  02110-1301, USA.
 */

defined( 'BLUDIT' ) || die( 'That did not work as expected.' );

define( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG', false );

define( 'CNQ_ONE_HOUR',   (60 * 60 ) );
define( 'CNQ_ONE_DAY',    CNQ_ONE_HOUR * 24 );
define( 'CNQ_ONE_WEEK',   CNQ_ONE_DAY * 7 );

class chucknorrisquotes extends Plugin {

    protected $categories = array();
    protected $categories_last_fetch = 0;
    protected $quote = '';
    protected $quote_last_fetch = '';

    protected $interval_values = array(
        'interval-hourly',
        'interval-daily',
        'interval-weekly',
        'interval-none',
    );
    public function init()  {
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        $this->dbFields = array(
            'interval' => 'interval-daily',
            'request_uri' => 'https://api.chucknorris.io/jokes/random',
            'category' => '-any-category-',
            'category_uri' => 'https://api.chucknorris.io/jokes/categories',
        );
    }
    public function post() {
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        if ( isset( $_POST['save'] ) ) {
            if ( empty( $_POST['interval'] ) ) {
                $interval = 'some-weird-string-lol';
            } else {
                $interval = $_POST['interval'];
            }
            if ( empty( $_POST['category'] ) ) {
                $category = 'some-weird-category-lol';
            } else {
                $category = $_POST['category'];
            }
            if ( empty( $_POST['request_uri'] ) ) {
                $the_uri = 'some-weird-uri-lol';
            } else {
                $the_uri = $_POST['request_uri'];
            }
            $remove_current_quote = false;
            if ( $interval != $this->getInterval() ) {
                // Interval has changed, remove current quote
                if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
                    error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Interval changed, removing current quote' );
                }
                $remove_current_quote = true;
            } elseif ( $category != $this->getCategory() ) {
                // Category has changed, remove current quote
                if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
                    error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Category changed, removing current quote' );
                }
                $remove_current_quote = true;
            } elseif ( $the_uri != $this->getRequestURI() ) {
                if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
                    error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): URI changed, removing current quote' );
                }
                $remove_current_quote = true;
            }
            if ( $remove_current_quote && ! empty( $this->workspace() ) ) {
                $ourfile = $this->workspace() . 'thequote.json';
                if ( @ unlink( $ourfile ) === false ) {
                    error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Unable to remove old "' . $ourfile . '"' );
                }
            }
        }
        parent::post();
    }

    // Inspirational credit/kudos: WordPress Core
    // Function to generate the regex pattern for parsing [shortcode_tag] shortcodes, similar to WordPress' shortcode regex
    private function getCustomTagRegEx() {
        $tag_name = 'chucknorrisquote';
        return(
            '\\['                              // Opening bracket
            . '(\\[?)'                         // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tag_name)"                    // 2: Shortcode name (e.g., "mytag")
            . '(\\b[^\\]]*?)'                  // 3: Attributes (if any), non-greedy
            . '(?:(\\/)|'                      // 4: Self-closing tag ...
            . '\\](.*?)'                       // 5: ...or closing bracket and content inside
            . '\\[\\/\\2\\])?'                 // Closing shortcode (optional for self-closing tags)
            . '(\\]?)' );                      // 6: Optional second closing bracket for escaping shortcodes: [[tag]]
    }
    // Process [shortcode_tag] shortcodes similar to WordPress' shortcode handling structure
    private function processCustomTags( $content, $replacement ) {
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Processing content, quote is "' . $replacement . '"' );
        }
        $pattern = $this->getCustomTagRegEx();
        $callback = function( $matches ) use( $replacement ) {
            $tag = $matches[2];
            $content = isset( $matches[5] ) ? $matches[5] : null;
            if ( $matches[3] === '/' ) {
                return( $replacement );
            } else {
                $processed_content = $this->processCustomTags( $content, $replacement );
                return( $replacement );
            }
        };
        return( preg_replace_callback( "/$pattern/s", $callback, $content ) );
    }
    // Make sure we skip content insdide <pre>..</pre>
    private function preProcessCustomTags( $content, $replacement ) {
        // Split the content by <pre> tags, we will skip the content inside <pre> tags
        $parts = preg_split( '/(<pre.*?>.*?<\/pre>)/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
        foreach( $parts as &$part ) {
            // If the part is not inside a <pre> tag, process the shortcodes
            if (!preg_match('/^<pre.*?>.*?<\/pre>$/is', $part)) {
                $part = $this->processCustomTags( $part, $replacement );
            }
        }
        return( implode('', $parts) );
    }
    /*
    public function beforeAdminLoad() {
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    /*
    public function afterAdminLoad() {
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    /*
    public function beforeSiteLoad() {
        global $staticContent;
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    /*
    public function afterSiteLoad() {
        global $staticContent;
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    /*
    public function afterAdminLoad() {
        global $staticContent;
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    /*
    public function beforeAll() {
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    /*
    public function siteBodyBegin() {
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    /*
    public function siteBodyEnd() {
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    protected function processContent() {
        $text = ob_get_clean();
        if ( $text !== false ) {
            if ( ! $this->getTheQuote() ) {
                if ( ! $this->fetchTheQuote() ) {
                    $the_quote = '';
                } else {
                    $the_quote = $this->quote;
                }
            } else {
                $the_quote = $this->quote;
            }
            if ( ! empty( $the_quote ) ) {
                $text = $this->preProcessCustomTags( $text, htmlentities( $the_quote ) );
            }
            echo $text;
        }
    }
    public function pageBegin() {
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        ob_start();
    }
    public function pageEnd() {
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        $this->processContent();
    }

    protected function check_uri( $uri ) {
        $invalid_URI = false;
        if ( function_exists( 'mb_strpos' ) ) {
            if ( mb_strpos( $the_uri, 'https://' ) !== 0 ) {
                if ( mb_strpos( $the_uri, 'http://' ) !== 0 ) {
                    $invalid_URI = true;
                } elseif ( mb_strlen( $the_uri ) < 10 ) {
                    $invalid_URI = true;
                }
            } elseif ( mb_strlen( $the_uri ) < 11 ) {
                $invalid_URI = true;
            }
        } else {
            if ( strpos( $the_uri, 'https://' ) !== 0 ) {
                if ( strpos( $the_uri, 'http://' ) !== 0 ) {
                    $invalid_URI = true;
                } elseif ( strlen( $the_uri ) < 10 ) {
                    $invalid_URI = true;
                }
            } elseif ( strlen( $the_uri ) < 11 ) {
                $invalid_URI = true;
            }
        }
        return( $invalid_URI );
    }
    // Categories, read/write
    protected function fetchCategoriesFromWorkspace() {
        $ourfile = $this->workspace() . 'categories.json';
        $the_data = file_get_contents( $ourfile );
        if ( $the_data !== false ) {
            try {
                $the_json = @ json_decode( $the_data, true, 3 );
                if ( ! is_array( $the_json ) ) {
                    error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): json_decode() returned error, "' . json_last_error_msg() . '"' );
                    $the_json = false;
                }
            } catch( \Throwable $e ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Exception while decoding JSON, "' . $e->getMessage() . '"' );
                $the_json = false;
            }
            if ( empty( $the_json['lastFetch'] ) ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Data does not contain "lastFetch"' );
                $the_json = false;
            }
            if ( empty( $the_json['categories'] ) ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Data does not contain "categories"' );
                $the_json = false;
            }
            if ( ! is_array( $the_json ) ) {
                return( false );
            }
            $fetch_date = date( 'YmdHis', $the_json['lastFetch'] );
            $now_date = date( 'YmdHis', ( time() - CNQ_ONE_DAY ) );
            if ( $fetch_date < $now_date ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Categories needs to be refreshed' );
                return( false );
            }
            $this->categories = $the_json['categories'];
            $this->categories_last_fetch = $the_json['lastFetch'];
            return( true );
        }
        return( false );
    }
    protected function storeCategoriesToWorkspace( $categories ) {
        if ( ! is_array( $categories ) ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): $categories needs to be an array' );
            return( false );
        }
        error_log('Storing categories');
        $now = time();
        $now_date = date( 'YmdHis', $now );
        $categories_store = [ 'lastFetch' => $now, 'categories' => $categories ];
        try {
            $json_data = @ json_encode( $categories_store );
            if ( $json_data === false ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): json_encode() returned error, "' . json_last_error_msg() . '"' );
            }
        } catch( \Throwable $e ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Exception while encoding JSON, "' . $e->getMessage() . '"' );
            $json_data = false;
        }
        if ( $json_data === false ) {
            return( false );
        }
        $ourfile = $this->workspace() . 'categories.json';
        if ( file_put_contents( $ourfile, $json_data ) === false ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Unable to update "' . $ourfile . '"' );
            return( false );
        }
        $this->categories = $categories;
        $this->categories_last_fetch = $now;
        return( true );
    }
    // Quote, read/write
    protected function getTheQuote() {
        switch( $this->getInterval() ) {
            case 'interval-hourly':
                $timer = CNQ_ONE_HOUR;
                break;
            case 'interval-weekly':
                $timer = CNQ_ONE_WEEK;
                break;
            case 'interval-none':
                $timer = 0;
                break;
            default:
                $timer = CNQ_ONE_DAY;
                break;
        }// switch
        if ( $timer == 0 ) {
            $this->quote = '';
            // Temporarily disabled, nothing to return
            return( true );
        }
        // Try to fetch existing quote
        $ourfile = $this->workspace() . 'thequote.json';
        $the_data = file_get_contents( $ourfile );
        if ( $the_data !== false ) {
            $the_json = @ json_decode( $the_data, true, 2 );
            if ( ! is_array( $the_json ) || empty( $the_json['quote'] ) ) {
                return( false );
            }
            if ( empty( $the_json['lastFetch'] ) ) {
                return( false );
            }
            // Check timestamp
            $fetch_date = date( 'YmdHis', $the_json['lastFetch'] );
            $now_date = date( 'YmdHis', ( time() - $timer ) );
            if ( $fetch_date >= $now_date ) {
                // We're good
                $this->quote = $the_json['quote'];
                $this->quote_last_fetch = $the_json['lastFetch'];
                return( true );
            }
        }
        return( false );
    }
    protected function fetchTheQuote() {
        $this->quote = '';
        // Do some sanity checks
        if ( $this->getInterval() == 'interval-none' ) {
            return( true );
        }
        $the_uri = $this->getRequestURI();
        $invalid_URI = ! $this->check_uri( $the_uri );
        if ( $invalid_URI ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): "' . $the_uri . '" is not valid, not fetching' );
            return( false );
        }
        // Fetch from remote
        $category = $this->getCategory();
        if ( ! empty( $category ) && $category != '-any-category-' ) {
            $the_uri .= '?category=' . urlencode( $category );
        }
        if ( defined( 'CHUCKNORRISQUOTES_PLUGIN_DEBUG' ) && CHUCKNORRISQUOTES_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Fetching "' . $the_uri . '"' );
        }
        $fetch_data = TCP::http( $the_uri, 'GET', true, 10, true, true, false );
        if ( $fetch_data === false ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Unable to fetch data from "' . $the_uri . '"' );
            return( false );
        }
        // Decode response
        try {
            $the_quote = @ json_decode( $fetch_data, true, 3 );
            if ( ! is_array( $the_quote ) ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): json_decode() returned error, "' . json_last_error_msg() . '"' );
                $the_quote = false;
            } elseif ( empty( $the_quote['value'] ) ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Data does not contain "value"' );
                error_log( $fetch_data );
                $the_quote = false;
            }
        } catch( \Throwable $e ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Exception while decoding JSON, "' . $e->getMessage() . '"' );
            $the_quote = false;
        }
        // Process response
        $now = time();
        $now_date = date( 'YmdHis', $now );
        $quote_store = [ 'lastFetch' => $now, 'quote' => $the_quote['value'] ];
        try {
            $json_data = @ json_encode( $quote_store );
            if ( $json_data === false ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): json_encode() returned error, "' . json_last_error_msg() . '"' );
            }
        } catch( \Throwable $e ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Exception while encoding JSON, "' . $e->getMessage() . '"' );
            $json_data = false;
        }
        if ( $json_data === false ) {
            return( false );
        }
        // Store quote
        $ourfile = $this->workspace() . 'thequote.json';
        if ( file_put_contents( $ourfile, $json_data ) === false ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Unable to update "' . $ourfile . '"' );
            return( false );
        }
        $this->quote = $the_quote['value'];
        $this->quote_last_fetch = $now;
        return( true );
    }

    // Form
    public function form() {
        global $L;
        global $site;

        $html = '';
        // Interval
        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8 mb-3">';
        $html .= '<label class="form-label" for="interval">' . $L->get( 'interval' ) . '</label>';
        $html .= '<select class="form-select" id="page" name="interval" aria-describedby="intervalHelp">';
        foreach( $this->interval_values as $k ) {
            $html .= '<option value="' . $k . '"';
            if ( $this->getInterval() == $k ) {
                $html .= ' selected';
            }
            $html .= '>' . $L->get( $k ) . '</option>';
        }
        $html .= '</select>';
        $html .= '<div id="intervalHelp" class="form-text">' . $L->get( 'help-interval' ) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        // Request URI
        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8 mb-3">';
        $the_uri = $this->getRequestURI();
        $invalid_URI = ! $this->check_uri( $the_uri );
        $nofetch_URI = false;
        if ( ! $invalid_URI ) {
            if ( ! $this->getTheQuote() ) {
                if ( ! $this->fetchTheQuote() ) {
                    $nofetch_URI = true;
                }
            }
        }
        $html .= '<label class="form-label" for="request_uri">' . $L->get( 'request-uri' ) . '</label>';
        if ( $invalid_URI ) {
            $html .= '<div class="form-text text-danger">' . $L->get('error-invalid-uri') . '</div>';
        }
        if ( $nofetch_URI ) {
            $html .= '<div class="form-text text-danger">' . $L->get('error-nofetch-uri') . '</div>';
        }
        $html .= '<input name="request_uri" type="text" class="form-control" aria-describedby="requestURIHelp" value="' . $this->getRequestURI() . '" />';
        $html .= '<div id="requestURIHelp" class="form-text">' . $L->get( 'help-requesturi' ) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        // Categories URI
        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8 mb-3">';
        $invalid_category_URI = ! $this->check_uri( $the_uri );
        $nofetch_category_URI = false;
        $invalid_categories = false;
        if ( ! $this->fetchCategoriesFromWorkspace() ) {
            $the_uri = $this->getCategoryURI();
            $categories = false;
            if ( ! $invalid_URI ) {
                $category_data = TCP::http( $the_uri, 'GET', true, 10, true, true, false );
                if ( $category_data === false ) {
                    $nofetch_category_URI = true;
                } else {
                    try {
                        $categories = @ json_decode( $category_data, true, 2 );
                    } catch( \Throwable $e ) {
                        error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Exception while decoding JSON, "' . $e->getMessage() . '"' );
                        $categories = false;
                    }
                    if ( ! is_array( $categories ) ) {
                        $invalid_categories = true;
                    } else {
                        if ( ! $this->storeCategoriesToWorkspace( $categories ) ) {
                            $nostore_categories = true;
                        }
                    }
                }
            }
        } else {
            $categories = $this->categories;
        }
        $html .= '<label class="form-label" for="category_uri">' . $L->get( 'category-uri' ) . '</label>';
        if ( $invalid_category_URI ) {
            $html .= '<div class="form-text text-danger">' . $L->get('error-invalid-category-uri') . '</div>';
        }
        if ( $nofetch_category_URI ) {
            $html .= '<div class="form-text text-danger">' . $L->get('error-nofetch-category-uri') . '</div>';
        }
        if ( $nostore_categories ) {
            $html .= '<div class="form-text text-danger">' . $L->get('error-no-store-categories') . '</div>';
        }
        $html .= '<input name="category_uri" type="text" class="form-control" aria-describedby="categoryURIHelp" value="' . $this->getCategoryURI() . '" />';
        $html .= '<div id="requestURIHelp" class="form-text">' . $L->get( 'help-categoryuri' ) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        // Category
        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8 mb-5">';
        $html .= '<label class="form-label" for="category">' . $L->get( 'category' ) . '</label>';
        if ( $invalid_categories ) {
            $html .= '<div class="form-text text-danger">' . $L->get('error-invalid-categories') . '</div>';
        } else {
            if ( ! empty( $this->categories_last_fetch ) ) {
                $html .= '<div class="form-text text-success small">(' . $L->get('categories-last-fetched') . ': ' . date( 'Y-m-d, H:i:s', $this->categories_last_fetch ) . ')</div>';
            }
        }
        $html .= '<select class="form-select" id="page" name="category" aria-describedby="categoryHelp">';
        if ( is_array( $categories ) ) {
            $html .= '<option value="-any-category-"';
            if ( $this->getCategory() == '-any-category-' ) {
                $html .= ' selected';
            }
            $html .= '>' . $L->get('any-category') . '</option>';
            foreach( $categories as $k ) {
                $html .= '<option value="' . htmlentities( $k ) . '"';
                if ( $this->getCategory() == $k ) {
                    $html .= ' selected';
                }
                $html .= '>' . htmlentities( $k ) . '</option>';
            }
        } else {
            $html .= '<option value="">' . $L->get('no-category' ) . '</option>';
        }
        $html .= '</select>';
        $html .= '<div id="categoryHelp" class="form-text">' . $L->get( 'help-category' ) . '</div>';
        $html .= '</div>';
        $html .= '</div>';

        // Possibly show current quote
        if ( $this->getTheQuote() && ! empty( $this->quote ) ) {
            $date_format = $site->dateFormat();
            if ( empty( $date_format ) ) {
                $date_format = 'Y-m-d, H:i:s';
            }
            $html .= '<div class="row">';
            $html .= '<div class="col-12 col-lg-10 col-xl-8 p-3 alert alert-primary" role="alert" style="max-width: 65% !important; margin-left: 25px !important;">';
            $html .= '<span><b>' . $L->get('show-last-quote') . '</b></span><small> (' . date( $date_format, $this->quote_last_fetch ) . ')</small><br/><br/>';
            $html .= '<blockquote class="blockquote">' . $this->quote . '</blockquote>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8 p-3 alert alert-info" role="alert" style="max-width: 65% !important; margin-left: 25px !important;">';
        $html .= '<p class="h3">' . $L->get( 'usage-header' ) . '</p>';
        $html .= '<p>' . $L->get( 'usage-help' ) . '</p>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8 p-3 alert alert-warning" role="alert" style="max-width: 65% !important; margin-left: 25px !important;">';
        $html .= '<p class="h2">&#9888;&#65039;</p>';
        $html .= '<p>' . $L->get( 'quote-disclaimer' ) . '</p>';
        $html .= '<p>' . $L->get( 'quote-sponsor' ) . '</p>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function getInterval() {
        return( $this->getValue( 'interval' ) );
    }
    public function getRequestURI() {
        return( $this->getValue( 'request_uri' ) );
    }
    public function getCategoryURI() {
        return( $this->getValue( 'category_uri' ) );
    }
    public function getCategory() {
        return( $this->getValue( 'category' ) );
    }

}
