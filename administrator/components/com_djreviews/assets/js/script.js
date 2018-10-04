/**
 * @package DJ-Reviews
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * 
 * DJ-Reviews is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 * 
 * DJ-Reviews is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * DJ-Reviews. If not, see <http://www.gnu.org/licenses/>.
 * 
 */

var DJReviewsTools = {
	validateNumber : function(input) {

		var number = input.getProperty('value');
		
		// valid format
		var valid_number = new RegExp(/^(\d+|\d+\.\d+)$/);
		
		// comma instead of dot
		var wrong_decimal = new RegExp(/\,/g);
		
		// non allowed characters
		var restricted = new RegExp(/[^\d+\.]/g);
		
		// replace comma with a dot
		number = number.replace(wrong_decimal, ".");
		
		if (valid_number.test(number) == false) {
			// remove illegal chars
			number = number.replace(restricted, '');
		}
		
		if (valid_number.test(number) == false) {
			// too many dots in here
			parts = number.split('.');
			if (parts.length > 2 ) {
				number = parts[0] + '.' + parts[1];
			}
		}
		
		input.setProperty('value', number);
	}
};

window.addEvent('domready', function() {
	var numberInputs = document.id(document.body).getElements('.djrevs-number');
	if (numberInputs.length > 0) {
		numberInputs.each(function(element, index) {
			element.addEvents({
				'keyup' : function(e){DJReviewsTools.validateNumber(element);},
				'change' : function(e){DJReviewsTools.validateNumber(element);},
				'click' : function(e){DJReviewsTools.validateNumber(element);}
			});
		});
	}
});

