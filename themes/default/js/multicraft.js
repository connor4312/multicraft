multicraft = {}; // Globally scoped object for talking to other incline Yii Js

$(document).ready(function() {

	/**
	 * Function to get LESS vars via the scooper. This nifty solution based off:
	 * http://stackoverflow.com/questions/10362445/passing-less-variable-to-javascript
	 */
	window.multicraft.less = {};
	$.each(document.styleSheets,function(i,sheet) {
		$.each(sheet.cssRules,function(i,rule) {
			var sRule = rule.cssText;
			if (rule.selectorText == "#scooper") {

				parts = rule.style.content.replace('\'', '').split('|');
				for (i in parts) {
					n = parts[i].split(':');
					window.multicraft.less[n[0]] = n[1];
				}
				window.multicraft.less['font-family-sans-serif'] = rule.style['font-family'];

				return true;
			}
		});
	});

	/* Page bindings and effects **********************************************/
	$('.hint').popover({
		trigger: 'hover',
		delay: 100,
		container: 'body'
	});

	$('[data-focus]').focus();

	$('[data-link]').click(function(e) {
		var target = $(this).attr('data-link');
		if (e.button === 0 && !e.ctrlKey) {
			e.preventDefault();
			window.location = target;
		} else {
			window.open(target);
		}
	});

	$('.dial').each(function() {
		var data = {};
		var n = $(this).attr('data-append');

		data.inline = false;
		data.thickness = 0.05;
		data.font = window.multicraft.less['font-family-sans-serif'];
		data.fontWeight = window.multicraft.less['headings-font-weight'];
		data.fgColor = window.multicraft.less['brand-primary'];
		data.inputColor = '#666';
		data.bgColor = '#ccc';

		$(this).knob(data);
	});

	/* Updates on the statistics page *****************************************/

	if ($('#player_dial').length) {
		var $dial = $('#player_dial');
		var $players = $('#players');
		var $percent = $('#player_percent');
		var players_total = $dial.attr('data-max');

		setInterval(function() {
			var players = $players.html();
			$dial.val(players).trigger('change');
			$percent.html(Math.round(players / players_total * 100).toString());
		}, 5000);
	}

	/* Console and chat functions *********************************************/

	var $console = $('#console');

	if ($console) {
		// $console.on('click', '.sx-ip-addr', function() {
		// 	var ip = $(this).html();
		// 	$.get('http://freegeoip.net/json/' + ip, function(data) {
		// 		alert(ip + ' is located in: ' + data.city + ', ' + data.region_name + ', ' + data.country_name + '. This will be replaced with a nicer display later...');
		// 	});
		// });

		var console_type = $console ? $console.attr('data-type') : null;
		var all_rules = {
			base: [
				{ type: 'sx-date', pattern: /([0-9]{1,2}\.[0-9]{1,2} )?([0-9]{2}:){2}[0-9]{2}/g },
				{ type: 'sx-col-black', pattern: /(§|\?)0.+/g },
				{ type: 'sx-col-dblue', pattern: /(§|\?)1.+/g },
				{ type: 'sx-col-dgreen', pattern: /(§|\?)2.+/g },
				{ type: 'sx-col-daqua', pattern: /(§|\?)3.+/g },
				{ type: 'sx-col-dred', pattern: /(§|\?)4.+/g },
				{ type: 'sx-col-dpurple', pattern: /(§|\?)5.+/g },
				{ type: 'sx-col-gold', pattern: /(§|\?)6.+/g },
				{ type: 'sx-col-gray', pattern: /(§|\?)7.+/g },
				{ type: 'sx-col-dgray', pattern: /(§|\?)8.+/g },
				{ type: 'sx-col-blue', pattern: /(§|\?)9.+/g },
				{ type: 'sx-col-green', pattern: /(§|\?)a.+/g },
				{ type: 'sx-col-aqua', pattern: /(§|\?)b.+/g },
				{ type: 'sx-col-red', pattern: /(§|\?)c.+/g },
				{ type: 'sx-col-lpurple', pattern: /(§|\?)d.+/g },
				{ type: 'sx-col-yellow', pattern: /(§|\?)e.+/g },
				{ type: 'sx-col-white', pattern: /(§|\?)f.+/g },
				{ type: 'sx-col-obfuscated', pattern: /(§|\?)k.+/g },
				{ type: 'sx-col-bold', pattern: /(§|\?)l.+/g },
				{ type: 'sx-col-strike', pattern: /(§|\?)m.+/g },
				{ type: 'sx-col-underline', pattern: /(§|\?)n.+/g },
				{ type: 'sx-col-italic', pattern: /(§|\?)o.+/g },
				{ type: 'sx-hide', pattern: /(§|\?)./g }
				// { type: 'sx-ip-addr', pattern: /(?:[0-9]{1,3}\.){3}[0-9]{1,3}/g }
			],
			chat: [
				{ type: 'sx-uname', pattern: /&lt;[A-z0-9]*?&gt;/g }, // Player name
				{ type: 'sx-hide', pattern: /&lt;&gt;/g },
				{ type: 'sx-connection', pattern: /Player .+connected.+ /g }, // Connect/disconnect
			],
			log: [
				{ type: 'sx-source sx-source-multicraft', pattern: /\[Multicraft\]/g },
				{ type: 'sx-source sx-source-server', pattern: /\[Server\]/g },
				{ type: 'sx-source sx-source-connection', pattern: /\[(Dis)?(C|c)onnect\]/g },
				{ type: 'sx-level-info', pattern: /\ INFO /g },
				{ type: 'sx-level-warning', pattern: /\ WARNING /g },
				{ type: 'sx-level-severe', pattern: /\ SEVERE /g }
			]
		};
		var rules = $.merge(all_rules.base, all_rules[$console ? $console.attr('data-type') : null]);

		multicraft.console = function(response) {
			data = $('<div/>').text(response.chat || response.log ).html();

			var replacer = function(match, rule) {
				return '<span class="' + rule.type + '">' + match + '</span>';
			};

			for (var i = 0, l = rules.length; i < l; i++) {
				data = data.replace(rules[i].pattern, function (match) {
					return replacer(match, rules[i]);
				});
			}

			out = '<div class="console-row">' + data.replace(/\n|\r/g, '</div><div class="console-row">') + '</div>';
			
			$console.html(out);
			set_data(data, multicraft.console);
		};
	}
});