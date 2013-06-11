<?php
class CSSLEX {
	private $csslex = array();
	function __construct () {
		$csslex = array();

		$csslex['keyword'] = '(?:\@(?:import|page|media|charset))';

		# nl                      \n|\r\n|\r|\f ; a newline
		$csslex['newline'] = '\n|\r\n|\r|\f';

		# h                       [0-9a-f]      ; a hexadecimal digit
		$csslex['hex'] = '[0-9a-f]';

		# nonascii                [\200-\377]
		$csslex['non_ascii'] = '[\200-\377]';

		# unicode                 \\{h}{1,6}(\r\n|[ \t\r\n\f])?
		$csslex['unicode'] = '(?:(?:\\' . $csslex['hex'] . '{1,6})(?:\r\n|[ \t\r\n\f])?)';

		# escape                  {unicode}|\\[^\r\n\f0-9a-f]
		$csslex['escape'] = '(?:' . $csslex['unicode'] . '|\\[^\r\n\f0-9a-f])';

		# nmstart                 [_a-z]|{nonascii}|{escape}
		$csslex['nmstart'] = '(?:[_a-z]|' . $csslex['non_ascii'] . '|' . $csslex['escape'] . ')';

		# nmchar                  [_a-z0-9-]|{nonascii}|{escape}
		$csslex['nmchar'] = '(?:[_a-z0-9-]|' . $csslex['non_ascii'] . '|' . $csslex['escape'] . ')';

		# ident                   -?{nmstart}{nmchar}*
		$csslex['ident'] = '-?' . $csslex['nmstart'] . $csslex['nmchar'] . '*';

		# name                    {nmchar}+
		$csslex['name'] = $csslex['nmchar'] . '+';

		# hash
		$csslex['hash'] = '#' . $csslex['name'];

		# string1                 \"([^\n\r\f\\"]|\\{nl}|{escape})*\"  ; "string"
		$csslex['string1'] = '"(?:[^\"\\]|\\.)*"';

		# string2                 \'([^\n\r\f\\']|\\{nl}|{escape})*\'  ; 'string'
		$csslex['string2'] = "'(?:[^\'\\]|\\.)*'";

		# string                  {string1}|{string2}
		$csslex['string'] = '(?:' . $csslex['string1'] . '|' . $csslex['string2'] . ')';

		# num                     [0-9]+|[0-9]*"."[0-9]+
		$csslex['num'] = '(?:[0-9]*\.[0-9]+|[0-9]+)';

		# s                       [ \t\r\n\f]
		$csslex['space'] = '[ \t\r\n\f]';

		# w                       {s}*
		$csslex['whitespace'] = '(?:' . $csslex['space'] . '*)';

		# url special chars
		$csslex['url_special_chars'] = '[!#$%&*-~]';

		# url chars               ({url_special_chars}|{nonascii}|{escape})*
		$csslex['url_chars'] = sprintf('(?:%s|%s|%s)*', $csslex['url_special_chars'], $csslex['non_ascii'], $csslex['escape']);

		# url
		$csslex['url'] = sprintf('url\(%s(%s|%s)%s\)', $csslex['whitespace'], $csslex['string'], $csslex['url_chars'], $csslex['whitespace']);

		# comments
		# see http://www.w3.org/tr/css21/grammar.html
		$csslex['comment'] = '\/\*[^\*]*\*+([^\/\*][^\*]*\*+)*\/';

		# {e}{m}             {return ems;}
		# {e}{x}             {return exs;}
		# {p}{x}             {return length;}
		# {c}{m}             {return length;}
		# {m}{m}             {return length;}
		# {i}{n}             {return length;}
		# {p}{t}             {return length;}
		# {p}{c}             {return length;}
		# {d}{e}{g}          {return angle;}
		# {r}{a}{d}          {return angle;}
		# {g}{r}{a}{d}       {return angle;}
		# {m}{s}             {return time;}
		# {s}                {return time;}
		# {h}{z}             {return freq;}
		# {k}{h}{z}          {return freq;}
		# %                  {return percentage;}
		$csslex['unit'] = '(?:em|ex|px|cm|mm|in|pt|pc|deg|rad|grad|ms|s|hz|khz|%)';

		# {num}{unit|ident}                   {return number;}
		$csslex['quantity'] = sprintf('%s(?:%s%s|%s)?', $csslex['num'], $csslex['whitespace'], $csslex['unit'], $csslex['ident']);

		# "<!--"                  {return cdo;}
		# "-->"                   {return cdc;}
		# "~="                    {return includes;}
		# "|="                    {return dashmatch;}
		# {w}"{"                  {return lbrace;}
		# {w}"+"                  {return plus;}
		# {w}">"                  {return greater;}
		# {w}","                  {return comma;}
		$csslex['punc'] = '<!--|-->|~=|\|=|[\{\+>,:;]';

		$this->csslex = $csslex;
	}

	function __get ($name) {
		return isset($this->csslex[$name]) ? $this->csslex[$name] : null;
	}
}
