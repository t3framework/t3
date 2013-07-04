/**
 * Creates a CSSJanus object.
 * 
 * CSSJanus transforms CSS rules with horizontal relevance so that a left-to-right stylesheet can
 * become a right-to-left stylesheet automatically. Processing can be bypassed for an entire rule
 * or a single property by adding a / * @noflip * / comment above the rule or property.
 * 
 * @author "Trevor Parscal" <trevorparscal@gmail.com>
 * @author "Roan Kattouw" <roankattouw@gmail.com>
 * @author "Lindsey Simon" <elsigh@google.com>
 * @author "Roozbeh Pournader" <roozbeh@gmail.com>
 * @author "Bryon Engelhardt" <ebryon77@gmail.com>
 * 
 * @class
 * @constructor
 * @param {RegExp} regex Regular expression whose matches to replace by a token
 * @param {String} token Placeholder text
 */
function CSSJanus() {

	/* Private Members */

	var prepared = false,
		// Tokens
		temporaryToken = '`TMP`',
		noFlipSingleToken = '`NOFLIP_SINGLE`',
		noFlipClassToken = '`NOFLIP_CLASS`',
		commentToken = '`COMMENT`',
		// Patterns
		nonAsciiPattern = '[^\\u0020-\\u007e]',
		unicodePattern = '(?:(?:\\[0-9a-f]{1,6})(?:\\r\\n|\\s)?)',
		numPattern = '(?:[0-9]*\\.[0-9]+|[0-9]+)',
		unitPattern = '(?:em|ex|px|cm|mm|in|pt|pc|deg|rad|grad|ms|s|hz|khz|%)',
		directionPattern = 'direction\\s*:\\s*',
		urlSpecialCharsPattern = '[!#$%&*-~]',
		validAfterUriCharsPattern = '[\'"]?\\s*',
		nonLetterPattern = '(^|[^a-zA-Z])',
		charsWithinSelectorPattern = '[^\\}]*?',
		noFlipPattern = '\\/\\*\\s*@noflip\\s*\\*\\/',
		commentPattern = '\\/\\*[^*]*\\*+([^\\/*][^*]*\\*+)*\\/',
		escapePattern = '(?:' + unicodePattern + '|\\\\[^\\r\\n\\f0-9a-f])',
		nmstartPattern = '(?:[_a-z]|' + nonAsciiPattern + '|' + escapePattern + ')',
		nmcharPattern = '(?:[_a-z0-9-]|' + nonAsciiPattern + '|' + escapePattern + ')',
		identPattern = '-?' + nmstartPattern + nmcharPattern + '*',
		quantPattern = numPattern + '(?:\\s*' + unitPattern + '|' + identPattern + ')?',
		signedQuantPattern = '((?:-?' + quantPattern + ')|(?:inherit|auto))',
		fourNotationQuantPropsPattern = '((?:margin|padding|border-width)\\s*:\\s*)',
		fourNotationColorPropsPattern = '(-color\\s*:\\s*)',
		colorPattern = '(#?' + nmcharPattern + '+)',
		urlCharsPattern = '(?:' + urlSpecialCharsPattern + '|' + nonAsciiPattern + '|' + escapePattern + ')*',
		lookAheadNotOpenBracePattern = '(?!(' + nmcharPattern + '|\\r?\\n|\\s|#|\\:|\\.|\\,|\\+|>)*?{)',
		lookAheadNotClosingParenPattern = '(?!' + urlCharsPattern + '?' + validAfterUriCharsPattern + '\\))',
		lookAheadForClosingParenPattern = '(?=' + urlCharsPattern + '?' + validAfterUriCharsPattern + '\\))',
		// Regular expressions
		temporaryTokenRegExp = new RegExp( '`TMP`', 'g' ),
		commentRegExp = new RegExp( commentPattern, 'gi' ),
		noFlipSingleRegExp = new RegExp( '(' + noFlipPattern + lookAheadNotOpenBracePattern + '[^;}]+;?)', 'gi' ),
		noFlipClassRegExp = new RegExp( '(' + noFlipPattern + charsWithinSelectorPattern + '})', 'gi' ),
		directionLtrRegExp = new RegExp( '(' + directionPattern + ')ltr', 'gi' ),
		directionRtlRegExp = new RegExp( '(' + directionPattern + ')rtl', 'gi' ),
		leftRegExp = new RegExp( nonLetterPattern + '(left)' + lookAheadNotClosingParenPattern + lookAheadNotOpenBracePattern, 'gi' ),
		rightRegExp = new RegExp( nonLetterPattern + '(right)' + lookAheadNotClosingParenPattern + lookAheadNotOpenBracePattern, 'gi' ),
		leftInUrlRegExp = new RegExp( nonLetterPattern + '(left)' + lookAheadForClosingParenPattern, 'gi' ),
		rightInUrlRegExp = new RegExp( nonLetterPattern + '(right)' + lookAheadForClosingParenPattern, 'gi' ),
		ltrInUrlRegExp = new RegExp( nonLetterPattern + '(ltr)' + lookAheadForClosingParenPattern, 'gi' ),
		rtlInUrlRegExp = new RegExp( nonLetterPattern + '(rtl)' + lookAheadForClosingParenPattern, 'gi' ),
		cursorEastRegExp = new RegExp( nonLetterPattern + '([ns]?)e-resize', 'gi' ),
		cursorWestRegExp = new RegExp( nonLetterPattern + '([ns]?)w-resize', 'gi' ),
		fourNotationQuantRegExp = new RegExp( fourNotationQuantPropsPattern + signedQuantPattern + '(\\s+)' + signedQuantPattern + '(\\s+)' + signedQuantPattern + '(\\s+)' + signedQuantPattern, 'gi' ),
		fourNotationColorRegExp = new RegExp( fourNotationColorPropsPattern + colorPattern + '(\\s+)' + colorPattern + '(\\s+)' + colorPattern + '(\\s+)' + colorPattern, 'gi' ),
		bgHorizontalPercentageRegExp = new RegExp( '(background(?:-position)?\\s*:\\s*[^%]*?)(-?' + numPattern + ')(%\\s*(?:' + quantPattern + '|' + identPattern + '))', 'gi' ),
		bgHorizontalPercentageXRegExp = new RegExp( '(background-position-x\\s*:\\s*)(-?' + numPattern + ')(%)', 'gi' ),
		borderRadiusRegExp = new RegExp( '(border-radius\\s*:\\s*)([^;]*)', 'gi' );

	/* Private Methods */

	/**
	 * Inverts the horizontal value of a background position property.
	 * 
	 * @private
	 * @function
	 * @param {String} match Matched property
	 * @param {String} pre Text before value
	 * @param {String} value Horizontal value
	 * @param {String} post Text after value
	 * @return {String} Inverted property
	 */
	function calculateNewBackgroundPosition( match, pre, value, post ) {
		return pre + ( 100 - Number( value ) ) + post;
	}

	/**
	 * Inverts the horizontal value of a background position property.
	 * 
	 * @private
	 * @function
	 * @param {String} match Matched property
	 * @param {String} pre Text before value
	 * @param {String} value Horizontal value
	 * @param {String} post Text after value
	 * @return {String} Inverted property
	 */
	function calculateNewBorderRadius( match, pre, values ) {
		values = values.split( /\s+/g );
		switch ( values.length ) {
			case 4:
				values = [values[1], values[0], values[3], values[2]];
				break;
			case 3:
				values = [values[1], values[0], values[2]];
				break;
			case 2:
				values = [values[1], values[0]];
				break;
		}
		return pre + values.join( ' ' );
	}

	/* Methods */

	return {
		/**
		 * Transform a left-to-right stylesheet to right-to-left.
		 * 
		 * @method
		 * @param {String} css Stylesheet to transform
		 * @param {Boolean} swapLtrRtlInUrl Swap 'ltr' and 'rtl' in URLs
		 * @param {Boolean} swapLeftRightInUrl Swap 'left' and 'right' in URLs
		 * @return {String} Transformed stylesheet
		 */
		'transform': function( css, swapLtrRtlInUrl, swapLeftRightInUrl ) {
			// Tokenizers
			var noFlipSingleTokenizer = new Tokenizer( noFlipSingleRegExp, noFlipSingleToken ),
				noFlipClassTokenizer = new Tokenizer( noFlipClassRegExp, noFlipClassToken ),
				commentTokenizer = new Tokenizer( commentRegExp, commentToken );

			// Tokenize
			css = commentTokenizer.tokenize(
				noFlipClassTokenizer.tokenize(
					noFlipSingleTokenizer.tokenize(
						// We wrap tokens in ` , not ~ like the original implementation does.
						// This was done because ` is not a legal character in CSS and can only
						// occur in URLs, where we escape it to %60 before inserting our tokens.
						css.replace( '`', '%60' )
					)
				)
			);

			// Transform URLs
			if ( swapLtrRtlInUrl ) {
				// Replace 'ltr' with 'rtl' and vice versa in background URLs
				css = css
					.replace( ltrInUrlRegExp, '$1' + temporaryToken )
					.replace( rtlInUrlRegExp, '$1ltr' )
					.replace( temporaryTokenRegExp, 'rtl' );
			}
			if ( swapLeftRightInUrl ) {
				// Replace 'left' with 'right' and vice versa in background URLs
				 css = css
					.replace( leftInUrlRegExp, '$1' + temporaryToken )
					.replace( rightInUrlRegExp, '$1left' )
					.replace( temporaryTokenRegExp, 'right' );
			}

			// Transform rules
			css = css
				// Replace direction: ltr; with direction: rtl; and vice versa.
				.replace( directionLtrRegExp, '$1' + temporaryToken )
				.replace( directionRtlRegExp, '$1ltr' )
				.replace( temporaryTokenRegExp, 'rtl' )
				// Flip rules like left: , padding-right: , etc.
				.replace( leftRegExp, '$1' + temporaryToken )
				.replace( rightRegExp, '$1left' )
				.replace( temporaryTokenRegExp, 'right' )
				// Flip East and West in rules like cursor: nw-resize;
				.replace( cursorEastRegExp, '$1$2' + temporaryToken )
				.replace( cursorWestRegExp, '$1$2e-resize' )
				.replace( temporaryTokenRegExp, 'w-resize' )
				// Border radius
				.replace( borderRadiusRegExp, calculateNewBorderRadius )
				// Swap the second and fourth parts in four-part notation rules
				// like padding: 1px 2px 3px 4px;
				.replace( fourNotationQuantRegExp, '$1$2$3$8$5$6$7$4' )
				.replace( fourNotationColorRegExp, '$1$2$3$8$5$6$7$4' )
				// Flip horizontal background percentages
				.replace( bgHorizontalPercentageRegExp, calculateNewBackgroundPosition )
				.replace( bgHorizontalPercentageXRegExp, calculateNewBackgroundPosition );

			// Detokenize
			css = noFlipSingleTokenizer.detokenize(
				noFlipClassTokenizer.detokenize(
					commentTokenizer.detokenize( css )
				)
			);

			return css;
		}
	};
}

/**
 * Creates a tokenizer object.
 * 
 * This utility class is used by CSSJanus to protect strings by replacing them temporarily with
 * tokens and later transforming them back.
 * 
 * @author Trevor Parscal
 * @author Roan Kattouw
 * 
 * @class
 * @constructor
 * @param {RegExp} regex Regular expression whose matches to replace by a token
 * @param {String} token Placeholder text
 */
Tokenizer = function( regex, token ) {

	/* Private Members */

	var matches = [],
		index = 0;

	/* Private Methods */

	/**
	 * Adds a match.
	 * 
	 * @private
	 * @function
	 * @param {String} match Matched string
	 * @returns {String} Token to leave in the matched string's place
	 */
	function tokenizeCallback( match ) {
		matches.push( match );
		return token;
	}

	/**
	 * Gets a match.
	 * 
	 * @private
	 * @function
	 * @param {String} token Matched token
	 * @returns {String} Original matched string to restore
	 */
	function detokenizeCallback( token ) {
		return matches[index++];
	}

	/* Methods */

	return {
		/**
		 * Replace matching strings with tokens.
		 * 
		 * @method
		 * @param {String} str String to tokenize
		 * @return {String} Tokenized string
		 */
		'tokenize': function( str ) {
			return str.replace( regex, tokenizeCallback );
		},
		/**
		 * Restores tokens to their original values.
		 * 
		 * @method
		 * @param {String} str String previously run through tokenize()
		 * @return {String} Original string
		 */
		'detokenize': function( str ) {
			return str.replace( new RegExp( '(' + token + ')', 'g' ), detokenizeCallback );
		}
	};
};

/* Initialization */

var cssjanus = new CSSJanus();