<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 */

/**
 * This is a rewrite & update version of PHP port of CSSJanus, a utility that transforms CSS style sheets
 * written for LTR to RTL.
 *
 * The original Python version of CSSJanus is Copyright 2008 by Google Inc. and
 * is distributed under the Apache license.
 *
 * The original PHP version of CSSJanus: https://doc.wikimedia.org/mediawiki-core/master/php/html/CSSJanus_8php.html.
 *
 * Original code: http://code.google.com/p/cssjanus/source/browse/trunk/cssjanus.py
 * License of original code: http://code.google.com/p/cssjanus/source/browse/trunk/LICENSE
 * @author Khanh Le
 *
 */
require_once 'csslex.php';

class JACSSJanus {
	// Patterns defined as null are built dynamically by buildPatterns()

	private static $patterns = array(

	);

	public static function getPatterns () {
		self::buildPatterns();
		return self::$patterns;
	}
	/**
	 * Build patterns we can't define above because they depend on other patterns.
	 */
	private static function buildPatterns() {
		if ( isset( self::$patterns['token_delimiter'] ) ) {
			// Patterns have already been built
			return;
		}
		$csslex = new CSSLEX;

		$patterns =& self::$patterns;
		$patterns['token_delimiter'] = '`';
		$patterns['tmp_token'] = sprintf('%sTMP%s', $patterns['token_delimiter'], $patterns['token_delimiter']);
		$patterns['token_lines'] = sprintf('%sj%s', $patterns['token_delimiter'], $patterns['token_delimiter']);

		# global constant text strings for css value matches.
		$patterns['ltr'] = 'ltr';
		$patterns['rtl'] = 'rtl';
		$patterns['left'] = 'left';
		$patterns['right'] = 'right';

		# this is a lookbehind match to ensure that we don't replace instances
		# of our string token (left, rtl, etc...) if there's a letter in front of it.
		# specifically, this prevents replacements like 'background: url(bright.png)'.
		$patterns['lookbehind_not_letter'] = '(?<![a-za-z])';

		# this is a lookahead match to make sure we don't replace left and right
		# in actual classnames, so that we don't break the html/css dependencies.
		# read literally, it says ignore cases where the word left, for instance, is
		# directly followed by valid classname characters and a curly brace.
		# ex: .column-left {float: left} will become .column-left {float: right}
		$patterns['lookahead_not_open_brace'] = sprintf('(?!(?:%s|%s|%s|#|\:|\.|\,|\+|>)*?(\,|{))',
		                            $csslex->nmchar, $patterns['token_lines'], $csslex->space);


		# these two lookaheads are to test whether or not we are within a
		# background: url(here) situation.
		# ref: http://www.w3.org/tr/css21/syndata.html#uri
		$patterns['valid_after_uri_chars'] = sprintf("[\'\"]?%s", $csslex->whitespace);
		$patterns['lookahead_not_closing_paren'] = sprintf("(?!%s?%s\))", $csslex->url_chars,
		                                                $patterns['valid_after_uri_chars']);
		$patterns['lookahead_for_closing_paren'] = sprintf("(?=%s?%s\))", $csslex->url_chars,
		                                                $patterns['valid_after_uri_chars']);

		# compile a regex to swap left and right values in 4 part notations.
		# we need to match negatives and decimal numeric values.
		# the case of border-radius is extra complex, so we handle it separately below.
		# ex. 'margin: .25em -2px 3px 0' becomes 'margin: .25em 0 3px -2px'.

		$patterns['possibly_negative_quantity'] = sprintf('((?:-?%s)|(?:inherit|auto))', $csslex->quantity);
		$patterns['possibly_negative_quantity_space'] = sprintf('%s%s%s', $patterns['possibly_negative_quantity'],
		                                                $csslex->space,
		                                                $csslex->whitespace);
		$patterns['four_notation_quantity_re'] = sprintf('/%s%s%s%s/i',
		                                        $patterns['possibly_negative_quantity_space'],
		                                        $patterns['possibly_negative_quantity_space'],
		                                        $patterns['possibly_negative_quantity_space'],
		                                        $patterns['possibly_negative_quantity']
		                                       );
		$patterns['color'] = sprintf('(%s|%s)', $csslex->name, $csslex->hash);
		$patterns['color_space'] = sprintf('%s%s', $patterns['color'], $csslex->space);
		$patterns['four_notation_color_re'] = sprintf('/(-color%s:%s)%s%s%s(%s)/i',
		                                     $csslex->whitespace,
		                                     $csslex->whitespace,
		                                     $patterns['color_space'],
		                                     $patterns['color_space'],
		                                     $patterns['color_space'],
		                                     $patterns['color']
		                                    );

		# border-radius is very different from usual 4 part notation: abcd should
		# change to badc (while it would be adcb in normal 4 part notation), abc
		# should change to babc, and ab should change to ba
		$patterns['border_radius_re'] = sprintf('/((?:%s)?)border-radius(%s:%s)'
		                               .'(?:%s)?(?:%s)?(?:%s)?(?:%s)'
		                               .'(?:%s\/%s(?:%s)?(?:%s)?(?:%s)?(?:%s))?/i',$csslex->ident,
		                                                                          $csslex->whitespace,
		                                                                          $csslex->whitespace,
		                                                                          $patterns['possibly_negative_quantity_space'],
		                                                                          $patterns['possibly_negative_quantity_space'],
		                                                                          $patterns['possibly_negative_quantity_space'],
		                                                                          $patterns['possibly_negative_quantity'],
		                                                                          $csslex->whitespace,
		                                                                          $csslex->whitespace,
		                                                                          $patterns['possibly_negative_quantity_space'],
		                                                                          $patterns['possibly_negative_quantity_space'],
		                                                                          $patterns['possibly_negative_quantity_space'],
		                                                                          $patterns['possibly_negative_quantity']
		                              );

		# compile the cursor resize regexes
		$patterns['cursor_east_re'] = '/' . $patterns['lookbehind_not_letter'] . '([ns]?)e-resize/';
		$patterns['cursor_west_re'] = '/' . $patterns['lookbehind_not_letter'] . '([ns]?)w-resize/';

		# matches the condition where we need to replace the horizontal component
		# of a background-position value when expressed in horizontal percentage.
		# had to make two regexes because in the case of position-x there is only
		# one quantity, and otherwise we don't want to match and change cases with only
		# one quantity.
		$patterns['bg_horizontal_percentage_re'] = sprintf('/background(-position)?(%s:%s)'
		                                                   .'([^%%]*?)(%s)%%'
		                                                   .'(%s(?:%s|top|center|bottom))/',
		                                                   $csslex->whitespace,
		                                                   $csslex->whitespace,
		                                                   $csslex->num,
		                                                   $csslex->whitespace,
		                                                   $patterns['possibly_negative_quantity']
		                                                   );

		$patterns['bg_horizontal_percentage_x_re'] = sprintf('/background-position-x(%s:%s)(%s)%%/', $csslex->whitespace,
		                                                       $csslex->whitespace,
		                                                       $csslex->num);

		# non-percentage units used for css lengths
		$patterns['length_unit'] = '(?:em|ex|px|cm|mm|in|pt|pc)';
		# to make sure the lone 0 is not just starting a number (like "02") or a percentage like ("0 %");
		$patterns['lookahead_end_of_zero'] = sprintf('(?![0-9]|%s%%)', $csslex->whitespace);
		# a length with a unit specified. matches "0" too, as it's a length, not a percentage.
		$patterns['length'] = sprintf('(?:-?%s(?:%s%s)|0+%s)', $csslex->num,
		                                    $csslex->whitespace,
		                                    $patterns['length_unit'],
		                                    $patterns['lookahead_end_of_zero']);

		# zero length. used in the replacement functions.
		$patterns['zero_length'] = sprintf('/(?:-?0+(?:%s%s)|0+%s)$/', $csslex->whitespace,
		                                                      $patterns['length_unit'],
		                                                      $patterns['lookahead_end_of_zero']);

		# matches background, background-position, and background-position-x
		# properties when using a css length for its horizontal positioning.
		$patterns['bg_horizontal_length_re'] = sprintf('/background(-position)?(%s:%s)'
		                                      .'((?:.+?%s+)??)(%s)'
		                                      .'((?:%s+)(?:%s|top|center|bottom))/', $csslex->whitespace,
		                                                                            $csslex->whitespace,
		                                                                            $csslex->space,
		                                                                            $patterns['length'],
		                                                                            $csslex->space,
		                                                                            $patterns['possibly_negative_quantity']);

		$patterns['bg_horizontal_length_x_re'] = sprintf('/background-position-x(%s:%s)(%s)/', $csslex->whitespace,
		                                                  $csslex->whitespace,
		                                                  $patterns['length']);

		# matches the opening of a body selector.
		$patterns['body_selector'] = sprintf('body%s{%s', $csslex->whitespace, $csslex->whitespace);

		# matches anything up until the closing of a selector.
		$patterns['chars_within_selector'] = '[^\}]*?';

		# matches the direction property in a selector.
		$patterns['direction_re'] = sprintf('direction%s:%s', $csslex->whitespace, $csslex->whitespace);

		# these allow us to swap "ltr" with "rtl" and vice versa only within the
		# body selector and on the same line.
		$patterns['body_direction_ltr_re'] = sprintf('/(%s)(%s)(%s)(ltr)/i',
		                                    $patterns['body_selector'], 
		                                    $patterns['chars_within_selector'],
		                                    $patterns['direction_re']
		                                   );
		$patterns['body_direction_rtl_re'] = sprintf('/(%s)(%s)(%s)(rtl)/i',
		                                    $patterns['body_selector'],
		                                    $patterns['chars_within_selector'],
		                                    $patterns['direction_re']
		                                   );


		# allows us to swap "direction:ltr" with "direction:rtl" and
		# vice versa anywhere in a line.
		$patterns['direction_ltr_re'] = sprintf('/%s(ltr)/', $patterns['direction_re']);
		$patterns['direction_rtl_re'] = sprintf('/%s(rtl)/', $patterns['direction_re']);

		# we want to be able to switch left with right and vice versa anywhere
		# we encounter left/right strings, except inside the background:url(). the next
		# two regexes are for that purpose. we have alternate in_url versions of the
		# regexes compiled in case the user passes the flag that they do
		# actually want to have left and right swapped inside of background:urls.
		$patterns['left_re'] = sprintf('/%s((?:top|bottom)?)(%s)%s%s/i', $patterns['lookbehind_not_letter'],
		                                                      $patterns['left'],
		                                                      $patterns['lookahead_not_closing_paren'],
		                                                      $patterns['lookahead_not_open_brace']
		                     );
		$patterns['right_re'] = sprintf('/%s((?:top|bottom)?)(%s)%s%s/i', $patterns['lookbehind_not_letter'],
		                                                       $patterns['right'],
		                                                       $patterns['lookahead_not_closing_paren'],
		                                                       $patterns['lookahead_not_open_brace']);
		$patterns['left_in_url_re'] = sprintf('/%s(%s)%s/i', $patterns['lookbehind_not_letter'],
		                                          $patterns['left'],
		                                          $patterns['lookahead_for_closing_paren']);
		$patterns['right_in_url_re'] = sprintf('/%s(%s)%s/i', $patterns['lookbehind_not_letter'],
		                                           $patterns['right'],
		                                           $patterns['lookahead_for_closing_paren']);
		$patterns['ltr_in_url_re'] = sprintf('/%s(%s)%s/i', $patterns['lookbehind_not_letter'],
		                                         $patterns['ltr'],
		                                         $patterns['lookahead_for_closing_paren']);
		$patterns['rtl_in_url_re'] = sprintf('/%s(%s)%s/i', $patterns['lookbehind_not_letter'],
		                                         $patterns['rtl'],
		                                         $patterns['lookahead_for_closing_paren']);

		$patterns['comment_re'] = sprintf('/(%s)/i', $csslex->comment);

		$patterns['noflip_token'] = '\@noflip';
		# the noflip_token inside of a comment. for now, this requires that comments
		# be in the input, which means users of a css compiler would have to run
		# this script first if they want this functionality.
		$patterns['noflip_annotation'] = sprintf('\/\*%s%s%s\*\/', $csslex->whitespace,
		                                       $patterns['noflip_token'],
		                                       $csslex->whitespace);

		# after a noflip_annotation, and within a class selector, we want to be able
		# to set aside a single rule not to be flipped. we can do this by matching
		# our noflip annotation and then using a lookahead to make sure there is not
		# an opening brace before the match.
		$patterns['noflip_single_re'] = sprintf('/(%s%s[^;}]+;?)/i', $patterns['noflip_annotation'],
		                                                   $patterns['lookahead_not_open_brace']);

		# after a noflip_annotation, we want to grab anything up until the next } which
		# means the entire following class block. this will prevent all of its
		# declarations from being flipped.
		$patterns['noflip_class_re'] = sprintf('/(%s%s})/i', $patterns['noflip_annotation'],
		                                           $patterns['chars_within_selector']);

		# border-radis properties and their values
		$patterns['border_radius_tokenizer_re'] = sprintf('/((?:%s)?border-radius%s:[^;}]+;?)/i', $csslex->ident,
		                                                                                $csslex->whitespace);
		$patterns['gradient_re'] = sprintf('/%s[\.-]gradient%s\(/i', $csslex->ident, $csslex->whitespace);

	}

	/**
	 * Transform an LTR stylesheet to RTL
	 * @param $css String: stylesheet to transform
	 * @param $swapLtrRtlInURL Boolean: If true, swap 'ltr' and 'rtl' in URLs
	 * @param $swapLeftRightInURL Boolean: If true, swap 'left' and 'right' in URLs
	 * @return Transformed stylesheet
	 */
	public static function transform( $css, $swapLtrRtlInURL = false, $swapLeftRightInURL = false ) {
		self::buildPatterns();
		// We wrap tokens in ` , not ~ like the original implementation does.
		// This was done because ` is not a legal character in CSS and can only
		// occur in URLs, where we escape it to %60 before inserting our tokens.
		$css = str_replace( self::$patterns['token_delimiter'], '%60', $css );


		// Tokenize single line rules with /* @noflip */
		$noFlipSingle = new CSSJanus_Tokenizer( self::$patterns['noflip_single_re'], '`NOFLIP_SINGLE`' );
		$css = $noFlipSingle->tokenize( $css );

		// Tokenize class rules with /* @noflip */
		$noFlipClass = new CSSJanus_Tokenizer( self::$patterns['noflip_class_re'], '`NOFLIP_CLASS`' );
		$css = $noFlipClass->tokenize( $css );

		// Tokenize comments
		$comments = new CSSJanus_Tokenizer( self::$patterns['comment_re'], '`C`' );
		$css = $comments->tokenize( $css );

	  # Tokenize gradients since we don't want to mirror the values inside
		//$comments = new CSSJanus_Tokenizer( self::$patterns['comment_re']GradientMatcher(), '`GRADIENT`' );
		//$css = $comments->tokenize( $css );

		// LTR->RTL fixes start here
		$css = self::FixBodyDirectionLtrAndRtl( $css );

		if ( $swapLtrRtlInURL ) {
			$css = self::fixLtrRtlInURL( $css );
		}

		if ( $swapLeftRightInURL ) {
			$css = self::fixLeftRightInURL( $css );
		}
		$css = self::fixLeftAndRight( $css );
		$css = self::fixCursorProperties( $css );

		$css = self::fixBorderRadius( $css );
		# Since FourPartNotation conflicts with BorderRadius, we tokenize border-radius properties here.
		$border_radius_tokenizer = new CSSJanus_Tokenizer( self::$patterns['border_radius_tokenizer_re'], '`BORDER_RADIUS`' );
		$css = $border_radius_tokenizer->tokenize( $css );

		$css = self::fixFourPartNotation( $css );

		$css = $border_radius_tokenizer->detokenize( $css );

		$css = self::fixBackgroundPosition( $css );

		// Detokenize stuff we tokenized before
		$css = $comments->detokenize( $css );
		$css = $noFlipClass->detokenize( $css );
		$css = $noFlipSingle->detokenize( $css );

		return $css;
	}

	/**
	 * Replaces ltr with rtl and vice versa ONLY in the body direction.
	 *
	 */
	private static function FixBodyDirectionLtrAndRtl( $css ) {
		$css = preg_replace( self::$patterns['body_direction_ltr_re'], '\1\2\3' . self::$patterns['tmp_token'], $css );
		$css = preg_replace( self::$patterns['body_direction_rtl_re'], '\1\2\3' . self::$patterns['ltr'], $css );
		$css = str_replace( self::$patterns['tmp_token'], self::$patterns['rtl'], $css );

		return $css;
	}

	/**
	 * Flip rules like left: , padding-right: , etc.
	 */
	private static function fixLeftAndRight( $css ) {
		$css = preg_replace( self::$patterns['left_re'], '\1' . self::$patterns['tmp_token'], $css );
		$css = preg_replace( self::$patterns['right_re'], '\1' . self::$patterns['left'], $css );
		$css = str_replace( self::$patterns['tmp_token'], self::$patterns['right'], $css );

		return $css;
	}

	/**
	 * Replace 'left' with 'right' and vice versa in background URLs
	 */
	private static function fixleftrightinurl( $css ) {
		$css = preg_replace( self::$patterns['left_in_url_re'], self::$patterns['tmp_token'], $css );
		$css = preg_replace( self::$patterns['right_in_url_re'], self::$patterns['left'], $css );
		$css = str_replace( self::$patterns['tmp_token'], self::$patterns['right'], $css );

		return $css;
	}

	/**
	 * replace 'ltr' with 'rtl' and vice versa in background urls
	 */
	private static function fixltrrtlinurl( $css ) {
		$css = preg_replace( self::$patterns['ltr_in_url_re'], self::$patterns['tmp_token'], $css );
		$css = preg_replace( self::$patterns['rtl_in_url_re'], self::$patterns['ltr'], $css );
		$css = str_replace( self::$patterns['tmp_token'], self::$patterns['rtl'], $css );

		return $css;
	}

	/**
	 * flip east and west in rules like cursor: nw-resize;
	 */
	private static function fixcursorproperties( $css ) {
		$css = preg_replace( self::$patterns['cursor_east_re'], '\1' . self::$patterns['tmp_token'], $css );
		$css = preg_replace( self::$patterns['cursor_west_re'], '\1e-resize', $css );
		$css = str_replace( self::$patterns['tmp_token'], 'w-resize', $css );

		return $css;
	}

	/**
	 * Fixes border-radius and its browser-specific variants.
	 */
	private static function fixBorderRadius( $css ) {
//echo self::$patterns['border_radius_re']; die();		
		$css = preg_replace_callback(self::$patterns['border_radius_re'], array( 'self', 'reorderBorderRadius' ), $css );

		return $css;
	}

	/**
	 * Fixes border-radius and its browser-specific variants.
	 */
	private static function reorderBorderRadius( $matches ) {
	  $first_group = self::reorderBorderRadiusPart(array_slice ($matches, 3, 4));
  	$second_group = self::reorderBorderRadiusPart(array_slice  ($matches, 7));
  	if ($second_group == '') 
    	return sprintf('%sborder-radius%s%s', $matches[1], $matches[2], $first_group);
  	else
    	return sprintf('%sborder-radius%s%s / %s', $matches[1], $matches[2], $first_group, $second_group);
	}

	/**
	 * Fixes border-radius and its browser-specific variants.
	 */
	private static function reorderBorderRadiusPart( $ps ) {
	  # Remove any piece which may be 'None'
	  $part = array();
	  foreach ($ps as $p) {
	  	if ($p) $part[] = $p;
	  }
	  
	  if (count($part) == 4) {
	    return sprintf('%s %s %s %s', $part[1], $part[0], $part[3], $part[2]);
	  } elseif (count($part) == 3) {
	    return sprintf('%s %s %s %s', $part[1], $part[0], $part[1], $part[2]);
	  } elseif (count($part) == 2) {
	    return sprintf('%s %s', $part[1], $part[0]);
	  } elseif (count($part) == 1) {
	    return $part[0];
	  } elseif (count($part) == 0) {
	    return '';
	  } else {
	  	return null;
	  }
	}

	/**
	 * Swap the second and fourth parts in four-part notation rules like
	 * padding: 1px 2px 3px 4px;
	 *
	 * Unlike the original implementation, this function doesn't suffer from
	 * the bug where whitespace is not preserved when flipping four-part rules
	 * and four-part color rules with multiple whitespace characters between
	 * colors are not recognized.
	 * See http://code.google.com/p/cssjanus/issues/detail?id=16
	 */
	private static function fixFourPartNotation( $css ) {
		$css = preg_replace( self::$patterns['four_notation_quantity_re'], '\1 \4 \3 \2', $css );
		$css = preg_replace( self::$patterns['four_notation_color_re'], '\1\2 \5 \4 \3', $css );

		return $css;
	}

	/**
	 * Flip horizontal background percentages.
	 */
	private static function fixBackgroundPosition( $css ) {
		$css = preg_replace_callback( self::$patterns['bg_horizontal_percentage_re'],
			array( 'self', 'calculateNewBackgroundPosition' ), $css );
		$css = preg_replace_callback( self::$patterns['bg_horizontal_percentage_x_re'],
			array( 'self', 'calculateNewBackgroundPositionX' ), $css );
		$css = preg_replace_callback( self::$patterns['bg_horizontal_length_re'],
			array( 'self', 'calculateNewBackgroundLengthPosition' ), $css );
		$css = preg_replace_callback( self::$patterns['bg_horizontal_length_x_re'],
			array( 'self', 'calculateNewBackgroundLengthPositionX' ), $css );

		return $css;
	}

	/**
	 * Callback for calculateNewBackgroundPosition()
	 */
	private static function calculateNewBackgroundPosition( $matches ) {
	  # The flipped value is the offset from 100%
	  $new_x = 100-intval($matches[4]);

	  # Since m.group(1) may very well be None type and we need a string..
	  if ($matches[1]){
	    $position_string = $matches[1];
	  } else {
	    $position_string = '';
		}
	  return sprintf('background%s%s%s%s%%%s', $position_string, $matches[2], $matches[3], $new_x, $matches[5]);
	}

	/**
	 * Callback for calculateNewBackgroundPosition()
	 */
	private static function calculateNewBackgroundPositionX( $matches ) {
	  # The flipped value is the offset from 100%
	  $new_x = 100-intval($matches[2]);

	  return sprintf('background-position-x%s%s%%', $matches[1], $new_x);
	}

	/**
	 * Fixes horizontal background-position lengths.
	 * Return: A string with the horizontal background position set to 100%, if zero. 
	 */
	private static function calculateNewBackgroundLengthPosition( $matches ) {
	  # return original if error
	  if ($matches[4]) {
	    return $matches[0];
	  }

	  # Since m.group(1) may very well be None type and we need a string..
	  if ($matches[1]){
	    $position_string = $matches[1];
	  } else {
	    $position_string = '';
		}
	  return sprintf('background%s%s%s100%%%s', $position_string, $matches[2], $matches[3], $matches[5]);

	}

	/**
	 * Fixes background-position-x lengths
	 * Return: A string with the background-position-x set to 100%, if zero.
	 */
	private static function calculateNewBackgroundLengthPositionX( $matches ) {
	  # return original if error
	  if ($matches[2]) {
	    return $matches[0];
	  }
	  
  	return sprintf('background-position-x%s100%%', $matches[1]);
	}
}




/**
 * Utility class used by CSSJanus that tokenizes and untokenizes things we want
 * to protect from being janused.
 * @author Roan Kattouw
 */
class CSSJanus_Tokenizer {
	private $regex, $token;
	private $originals;

	/**
	 * Constructor
	 * @param $regex string Regular expression whose matches to replace by a token.
	 * @param $token string Token
	 */
	public function __construct( $regex, $token ) {
		$this->regex = $regex;
		$this->token = $token;
		$this->originals = array();
	}

	/**
	 * Replace all occurrences of $regex in $str with a token and remember
	 * the original strings.
	 * @param $str String to tokenize
	 * @return string Tokenized string
	 */
	public function tokenize( $str ) {
		return preg_replace_callback( $this->regex, array( $this, 'tokenizeCallback' ), $str );
	}

	private function tokenizeCallback( $matches ) {
		$this->originals[] = $matches[0];
		return $this->token;
	}

	/**
	 * Replace tokens with their originals. If multiple strings were tokenized, it's important they be
	 * detokenized in exactly the SAME ORDER.
	 * @param $str String: previously run through tokenize()
	 * @return string Original string
	 */
	public function detokenize( $str ) {
		// PHP has no function to replace only the first occurrence or to
		// replace occurrences of the same string with different values,
		// so we use preg_replace_callback() even though we don't really need a regex
		return preg_replace_callback( '/' . preg_quote( $this->token, '/' ) . '/',
			array( $this, 'detokenizeCallback' ), $str );
	}

	private function detokenizeCallback( $matches ) {
		$retval = current( $this->originals );
		next( $this->originals );

		return $retval;
	}
}