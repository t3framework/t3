<style type="text/css">
.pass {color: green;}
.fail {color: red;}

table.result {
  font-family: verdana,arial,sans-serif;
  font-size:11px;
  color:#333333;
  border-width: 1px;
  border-color: #666666;
  border-collapse: collapse;
}
table.result td {
  border-width: 1px;
  padding: 8px;
  border-style: solid;
  border-color: #666666;
  background-color: #ffffff;
  width: 400px;
}
table.result tr td:first-child {
  border-width: 1px;
  padding: 8px;
  border-style: solid;
  border-color: #666666;
  background-color: #dedede;
  width: 50px;
}

</style>

<?php
require_once 'ja.cssjanus.php';

$test = '';
$testcase = '';
$shouldbe = '';
$swap_ltr_rtl_in_url = False;
$swap_left_right_in_url = False;

function test () {
  global $test, $testcase, $shouldbe, $swap_ltr_rtl_in_url, $swap_left_right_in_url;

  $input = implode ("\n", $testcase);
  $expect = implode ("\n", $shouldbe);
  $output = JACSSJanus::transform ($input, $swap_ltr_rtl_in_url, $swap_left_right_in_url);
  $pass = ($output == $expect);
  $result = $pass ? '<span class="pass">pass</span>' : '<span class="fail">fail</span>';
?>
  <h2 class="<?php echo $pass ? 'pass':'fail' ?>"><?php echo $test ?></h2>
  <table class="result">
    <tr><td>Input</td><td><?php echo str_replace("\n", "<br />\n", $input) ?></td></tr>
    <tr><td>Expect</td><td><?php echo str_replace("\n", "<br />\n", $expect) ?></td></tr>
    <tr><td>Output</td><td><?php echo str_replace("\n", "<br />\n", $output) ?></td></tr>
    <tr><td>Result</td><td><?php echo $result ?></td></tr>
  </table>
  <br /><br />
<?php
}


$test = 'testPreserveComments';
$testcase = array('/* left /* right */left: 10px');
$shouldbe = array('/* left /* right */right: 10px');
test();

$testcase = array('/*left*//*left*/left: 10px');
$shouldbe = array('/*left*//*left*/right: 10px');
test();

$testcase = array('/* Going right is cool */\n#test {left: 10px}');
$shouldbe = array('/* Going right is cool */\n#test {right: 10px}');

$testcase = array('/* padding-right 1 2 3 4 */\n#test {left: 10px}\n/*right*/');
$shouldbe = array('/* padding-right 1 2 3 4 */\n#test {right: 10px}\n/*right*/');
test();

$testcase = array('/** Two line comment\n * left\n \*/\n#test {left: 10px}');
$shouldbe = array('/** Two line comment\n * left\n \*/\n#test {right: 10px}');
test();

$test = 'testPositionAbsoluteOrRelativeValues';
$testcase = array('left: 10px');
$shouldbe = array('right: 10px');
test();


$test = 'testFourNotation';
$testcase = array('padding: .25em 15px 0pt 0ex');
$shouldbe = array('padding: .25em 0ex 0pt 15px');
test();

$testcase = array('margin: 1px -4px 3px 2px');
$shouldbe = array('margin: 1px 2px 3px -4px');
test();

$testcase = array('padding:0 15px .25em 0');
$shouldbe = array('padding:0 0 .25em 15px');
test();

$testcase = array('padding: 1px 4.1grad 3px 2%');
$shouldbe = array('padding: 1px 2% 3px 4.1grad');
test();

$testcase = array('padding: 1px 2px 3px auto');
$shouldbe = array('padding: 1px auto 3px 2px');
test();

$testcase = array('padding: 1px inherit 3px auto');
$shouldbe = array('padding: 1px auto 3px inherit');
test();

# not really four notation
$testcase = array('#settings td p strong');
$shouldbe = $testcase;
test();

$test = 'testThreeNotation';
$testcase = array('margin: 1em 0 .25em');
$shouldbe = array('margin: 1em 0 .25em');
test();

$testcase = array('margin:-1.5em 0 -.75em');
$shouldbe = array('margin:-1.5em 0 -.75em');
test();

$test = 'testTwoNotation';
$testcase = array('padding: 1px 2px');
$shouldbe = array('padding: 1px 2px');
test();

$test = 'testOneNotation';
$testcase = array('padding: 1px');
$shouldbe = array('padding: 1px');
test();

$test = 'testDirection';
# we don't want direction to be changed other than in body
$testcase = array('direction: ltr');
$shouldbe = array('direction: ltr');
test();

# we don't want direction to be changed other than in body
$testcase = array('direction: rtl');
$shouldbe = array('direction: rtl');
test();

# we don't want direction to be changed other than in body
$testcase = array('input { direction: ltr }');
$shouldbe = array('input { direction: ltr }');
test();

$testcase = array('body { direction: ltr }');
$shouldbe = array('body { direction: rtl }');
test();

$testcase = array('body { padding: 10px; direction: ltr; }');
$shouldbe = array('body { padding: 10px; direction: rtl; }');
test();

$testcase = array('body { direction: ltr } .myClass { direction: ltr }');
$shouldbe = array('body { direction: rtl } .myClass { direction: ltr }');
test();

$testcase = array('body{\n direction: ltr\n}');
$shouldbe = array('body{\n direction: rtl\n}');
test();

$test = 'testDoubleDash';
$testcase = array('border-left-color: red');
$shouldbe = array('border-right-color: red');
test();

$testcase = array('border-right-color: red');
$shouldbe = array('border-left-color: red');
test();

# This is for compatibility strength, in reality CSS has no properties
# that are currently like this.
$test = 'testCSSProperty';
$testcase = array('alright: 10px');
$shouldbe = array('alright: 10px');
test();

$testcase = array('alleft: 10px');
$shouldbe = array('alleft: 10px');
test();

$test = 'testFloat';
$testcase = array('float: right');
$shouldbe = array('float: left');
test();

$testcase = array('float: left');
$shouldbe = array('float: right');
test();

$test = 'testUrlWithFlagOff';
$swap_ltr_rtl_in_url = False;
$swap_left_right_in_url = False;

$testcase = array('background: url(/foo/bar-left.png)');
$shouldbe = array('background: url(/foo/bar-left.png)');
test();

$testcase = array('background: url(/foo/left-bar.png)');
$shouldbe = array('background: url(/foo/left-bar.png)');
test();

$testcase = array('url("http://www.blogger.com/img/triangle_ltr.gif")');
$shouldbe = array('url("http://www.blogger.com/img/triangle_ltr.gif")');
test();

$testcase = array("url('http://www.blogger.com/img/triangle_ltr.gif')");
$shouldbe = array("url('http://www.blogger.com/img/triangle_ltr.gif')");
test();

$testcase = array("url('http://www.blogger.com/img/triangle_ltr.gif'  )");
$shouldbe = array("url('http://www.blogger.com/img/triangle_ltr.gif'  )");
test();

$testcase = array('background: url(/foo/bar.left.png)');
$shouldbe = array('background: url(/foo/bar.left.png)');
test();

$testcase = array('background: url(/foo/bar-rtl.png)');
$shouldbe = array('background: url(/foo/bar-rtl.png)');
test();

$testcase = array('background: url(/foo/bar-rtl.png); left: 10px');
$shouldbe = array('background: url(/foo/bar-rtl.png); right: 10px');
test();

$testcase = array('background: url(/foo/bar-right.png); direction: ltr');
$shouldbe = array('background: url(/foo/bar-right.png); direction: ltr');
test();

$testcase = array('background: url(/foo/bar-rtl_right.png);',
          'left:10px; direction: ltr');
$shouldbe = array('background: url(/foo/bar-rtl_right.png);',
          'right:10px; direction: ltr');
test();

$test = 'testUrlWithFlagOn';
$swap_ltr_rtl_in_url = True;
$swap_left_right_in_url = True;

$testcase = array('background: url(/foo/bar-left.png)');
$shouldbe = array('background: url(/foo/bar-right.png)');
test();

$testcase = array('background: url(/foo/left-bar.png)');
$shouldbe = array('background: url(/foo/right-bar.png)');
test();

$testcase = array('url("http://www.blogger.com/img/triangle_ltr.gif")');
$shouldbe = array('url("http://www.blogger.com/img/triangle_rtl.gif")');
test();

$testcase = array("url('http://www.blogger.com/img/triangle_ltr.gif')");
$shouldbe = array("url('http://www.blogger.com/img/triangle_rtl.gif')");
test();

$testcase = array("url('http://www.blogger.com/img/triangle_ltr.gif'  )");
$shouldbe = array("url('http://www.blogger.com/img/triangle_rtl.gif'  )");
test();

$testcase = array('background: url(/foo/bar.left.png)');
$shouldbe = array('background: url(/foo/bar.right.png)');
test();

$testcase = array('background: url(/foo/bright.png)');
$shouldbe = array('background: url(/foo/bright.png)');
test();

$testcase = array('background: url(/foo/bar-rtl.png)');
$shouldbe = array('background: url(/foo/bar-ltr.png)');
test();

$testcase = array('background: url(/foo/bar-rtl.png); left: 10px');
$shouldbe = array('background: url(/foo/bar-ltr.png); right: 10px');
test();

$testcase = array('background: url(/foo/bar-right.png); direction: ltr');
$shouldbe = array('background: url(/foo/bar-left.png); direction: ltr');
test();

$testcase = array('background: url(/foo/bar-rtl_right.png);',
          'left:10px; direction: ltr');
$shouldbe = array('background: url(/foo/bar-ltr_left.png);',
          'right:10px; direction: ltr');
test();

$test = 'testPadding';
$testcase = array('padding-right: bar');
$shouldbe = array('padding-left: bar');
test();

$testcase = array('padding-left: bar');
$shouldbe = array('padding-right: bar');
test();

$test = 'testMargin';
$testcase = array('margin-left: bar');
$shouldbe = array('margin-right: bar');
test();

$testcase = array('margin-right: bar');
$shouldbe = array('margin-left: bar');
test();

$test = 'testBorder';
$testcase = array('border-left: bar');
$shouldbe = array('border-right: bar');
test();

$testcase = array('border-right: bar');
$shouldbe = array('border-left: bar');
test();

$test = 'testCursor';
$testcase = array('cursor: e-resize');
$shouldbe = array('cursor: w-resize');
test();

$testcase = array('cursor: w-resize');
$shouldbe = array('cursor: e-resize');
test();

$testcase = array('cursor: se-resize');
$shouldbe = array('cursor: sw-resize');
test();

$testcase = array('cursor: sw-resize');
$shouldbe = array('cursor: se-resize');
test();

$testcase = array('cursor: ne-resize');
$shouldbe = array('cursor: nw-resize');
test();

$testcase = array('cursor: nw-resize');
$shouldbe = array('cursor: ne-resize');
test();

$test = 'testBGPosition';
$testcase = array('background: url(/foo/bar.png) top left');
$shouldbe = array('background: url(/foo/bar.png) top right');
test();

$testcase = array('background: url(/foo/bar.png) top right');
$shouldbe = array('background: url(/foo/bar.png) top left');
test();

$testcase = array('background-position: top left');
$shouldbe = array('background-position: top right');
test();

$testcase = array('background-position: top right');
$shouldbe = array('background-position: top left');
test();

$test = 'testBGPositionPercentage';
$testcase = array('background-position: 100% 40%');
$shouldbe = array('background-position: 0% 40%');
test();

$testcase = array('background-position: 0% 40%');
$shouldbe = array('background-position: 100% 40%');
test();

$testcase = array('background-position: 23% 0');
$shouldbe = array('background-position: 77% 0');
test();

$testcase = array('background-position: 23% auto');
$shouldbe = array('background-position: 77% auto');
test();

$testcase = array('background-position-x: 23%');
$shouldbe = array('background-position-x: 77%');
test();

$testcase = array('background-position-y: 23%');
$shouldbe = array('background-position-y: 23%');
test();

$testcase = array('background:url(../foo-bar_baz.2008.gif) no-repeat 75% 50%');
$shouldbe = array('background:url(../foo-bar_baz.2008.gif) no-repeat 25% 50%');
test();

$testcase = array('.test { background: 10% 20% } .test2 { background: 40% 30% }');
$shouldbe = array('.test { background: 90% 20% } .test2 { background: 60% 30% }');
test();

$testcase = array('.test { background: 0% 20% } .test2 { background: 40% 30% }');
$shouldbe = array('.test { background: 100% 20% } .test2 { background: 60% 30% }');
test();

$test = 'testDirectionalClassnames';
/*
"""Makes sure we don't unnecessarily destroy classnames with tokens in them.

Despite the fact that that is a bad classname in CSS, we don't want to
break anybody.
"""
*/
$testcase = array('.column-left { float: left }');
$shouldbe = array('.column-left { float: right }');
test();

$testcase = array('#bright-light { float: left }');
$shouldbe = array('#bright-light { float: right }');
test();

$testcase = array('a.left:hover { float: left }');
$shouldbe = array('a.left:hover { float: right }');
test();

#tests newlines
$testcase = array("#bright-left,\n.test-me { float: left }");
$shouldbe = array("#bright-left,\n.test-me { float: right }");
test();

#tests newlines
$testcase = array("#bright-left,", '.test-me { float: left }');
$shouldbe = array("#bright-left,", '.test-me { float: right }');
test();

#tests multiple names and commas
$testcase = array('div.leftpill, div.leftpillon {margin-right: 0 !important}');
$shouldbe = array('div.leftpill, div.leftpillon {margin-left: 0 !important}');
test();

$testcase = array('div.left > span.right+span.left { float: left }');
$shouldbe = array('div.left > span.right+span.left { float: right }');
test();

$testcase = array('.thisclass .left .myclass {background:#fff;}');
$shouldbe = array('.thisclass .left .myclass {background:#fff;}');
test();

$testcase = array('.thisclass .left .myclass #myid {background:#fff;}');
$shouldbe = array('.thisclass .left .myclass #myid {background:#fff;}');
test();


$test = 'testLongLineWithMultipleDefs';
$testcase = array('body{direction:rtl;float:right}
          .b2{direction:ltr;float:right}');
$shouldbe = array('body{direction:ltr;float:left}
          .b2{direction:ltr;float:left}');
test();

$test = 'testNoFlip';
# """Tests the /* @noflip */ annotation on classnames."""
$testcase = array('/* @noflip */ div { float: left; }');
$shouldbe = array('/* @noflip */ div { float: left; }');
test();

$testcase = array('/* @noflip */ div, .notme { float: left; }');
$shouldbe = array('/* @noflip */ div, .notme { float: left; }');
test();

$testcase = array('/* @noflip */ div { float: left; } div { float: left; }');
$shouldbe = array('/* @noflip */ div { float: left; } div { float: right; }');
test();

$testcase = array('/* @noflip */\ndiv { float: left; }\ndiv { float: left; }');
$shouldbe = array('/* @noflip */\ndiv { float: left; }\ndiv { float: right; }');
test();

# Test @noflip on single rules within classes
$testcase = array('div { float: left; /* @noflip */ float: left; }');
$shouldbe = array('div { float: right; /* @noflip */ float: left; }');
test();

$testcase = array('div\n{ float: left;\n/* @noflip */\n float: left;\n }');
$shouldbe = array('div\n{ float: right;\n/* @noflip */\n float: left;\n }');
test();

$testcase = array('div\n{ float: left;\n/* @noflip */\n text-align: left\n }');
$shouldbe = array('div\n{ float: right;\n/* @noflip */\n text-align: left\n }');
test();

$testcase = array('div\n{ /* @noflip */\ntext-align: left;\nfloat: left\n  }');
$shouldbe = array('div\n{ /* @noflip */\ntext-align: left;\nfloat: right\n  }');
test();

$testcase = array('/* @noflip */div{float:left;text-align:left;}div{float:left}');
$shouldbe = array('/* @noflip */div{float:left;text-align:left;}div{float:right}');
test();

$testcase = array('/* @noflip */','div{float:left;text-align:left;}a{foo:left}');
$shouldbe = array('/* @noflip */','div{float:left;text-align:left;}a{foo:right}');
test();

$test = 'testBorderRadiusNotation';
$testcase = array('border-radius: .25em 15px 0pt 0ex');
$shouldbe = array('border-radius: 15px .25em 0ex 0pt');
test();

$testcase = array('border-radius: 10px 15px 0px');
$shouldbe = array('border-radius: 15px 10px 15px 0px');
test();

$testcase = array('border-radius: 7px 8px');
$shouldbe = array('border-radius: 8px 7px');
test();

$testcase = array('border-radius: 5px');
$shouldbe = array('border-radius: 5px');
test();

$test = 'testGradientNotation';
$testcase = array('background-image: -moz-linear-gradient(#326cc1, #234e8c)');
$shouldbe = array('background-image: -moz-linear-gradient(#326cc1, #234e8c)');
test();

$testcase = array('background-image: -webkit-gradient(linear, 100% 0%, 0% 0%, from(#666666), to(#ffffff))');
$shouldbe = array('background-image: -webkit-gradient(linear, 100% 0%, 0% 0%, from(#666666), to(#ffffff))');
test();
