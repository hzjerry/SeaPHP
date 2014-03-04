/**
 * jQuery php strtr function
 * @character_set UTF-8
 * @author original by: Brett Zamir (http://brett-zamir.me)
 * @version 1.2013.04.25.1428
 * @link https://raw.github.com/kvz/phpjs/master/functions/strings/strtr.js
 *  Example
 * 	<code>
 *      example 1: $trans = {'hello' : 'hi', 'hi' : 'hello'};
 *      example 1: $.strtr('hi all, I said hello', $trans);
 *      returns 1: 'hello all, I said hi'
 *      example 2: $.strtr('äaabaåccasdeöoo', 'äåö','aao');
 *      returns 2: 'aaabaaccasdeooo'
 *      example 3: $.strtr('ääääääää', 'ä', 'a');
 *      returns 3: 'aaaaaaaa'
 *      example 4: $.strtr('http', 'pthxyz','xyzpth');
 *      returns 4: 'zyyx'
 *      example 5: $.strtr('zyyx', 'pthxyz','xyzpth');
 *      returns 5: 'http'
 *      example 6: $.strtr('aa', {'a':1,'aa':2});
 *      returns 6: '2'
 * 	</code>
 */
;(function($)
{
    $.extend(
    {
        /**
         * url get parameters
         * @public
         * @return array()
         */
        strtr:function(str, from, to)
        {
              var fr = '',
                i = 0,
                j = 0,
                lenStr = 0,
                lenFrom = 0,
                fromTypeStr = '',
                toTypeStr = '',
                istr = '';
              var tmpFrom = [];
              var tmpTo = [];
              var ret = '';
              var match = false;

              // Received replace_pairs?
              // Convert to normal from->to chars
              if (typeof from === 'object') {
                for (fr in from) {
                  if (from.hasOwnProperty(fr)) {
                    tmpFrom.push(fr);
                    tmpTo.push(from[fr]);
                  }
                }

                from = tmpFrom;
                to = tmpTo;
              }

              // Walk through subject and replace chars when needed
              lenStr = str.length;
              lenFrom = from.length;
              fromTypeStr = typeof from === 'string';
              toTypeStr = typeof to === 'string';

              for (i = 0; i < lenStr; i++) {
                match = false;
                if (fromTypeStr) {
                  istr = str.charAt(i);
                  for (j = 0; j < lenFrom; j++) {
                    if (istr == from.charAt(j)) {
                      match = true;
                      break;
                    }
                  }
                } else {
                  for (j = 0; j < lenFrom; j++) {
                    if (str.substr(i, from[j].length) == from[j]) {
                      match = true;
                      // Fast forward
                      i = (i + from[j].length) - 1;
                      break;
                    }
                  }
                }
                if (match) {
                  ret += toTypeStr ? to.charAt(j) : to[j];
                } else {
                  ret += str.charAt(i);
                }
              }
              return ret;
        },
    });
})(jQuery);