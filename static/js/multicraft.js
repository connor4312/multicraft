multicraft = {}; // Globally scoped object for talking to other incline Yii Js

$(document).ready(function() {

	/**
	 * Function to get LESS vars via the scooper. This nifty solution based off:
	 * http://stackoverflow.com/questions/10362445/passing-less-variable-to-javascript
	 */
	var less = {};
	$.each(document.styleSheets,function(i,sheet) {
		$.each(sheet.cssRules,function(i,rule) {
			var sRule = rule.cssText;
			if (rule.selectorText == "#scooper") {

				parts = rule.style.content.replace('\'', '').split('|');
				for (i in parts) {
					n = parts[i].split(':');
					less[n[0]] = n[1];
				}
				less['font-family-sans-serif'] = rule.style['font-family'];

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

	$('.dial').each(function() {
		var data = {};
		var n = $(this).attr('data-append');

		data.inline = false;
		data.thickness = 0.05;
		data.font = less['font-family-sans-serif'];
		data.fontWeight = less['headings-font-weight'];
		data.fgColor = less['brand-primary'];
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
	$('#console').on('click', '.sx-ip-addr', function() {
		var ip = $(this).html();
		$.get('http://freegeoip.net/json/' + ip, function(data) {
			alert(ip + ' is located in: ' + data.city + ', ' + data.region_name + ', ' + data.country_name + '. This will be replaced with a nicer display later...');
		});
	});

	multicraft.console = function(response, datapart) {
		var rules = {
			'sx-date': /([0-9]{1,2}\.[0-9]{1,2} )?([0-9]{2}:){2}[0-9]{2}/g,
			'sx-source sx-source-multicraft': /\[Multicraft\]/g,
			'sx-source sx-source-server': /\[Server\]/g,
			'sx-info': /\[INFO\]/g,
			'sx-warning': /\[WARNING\]/g,
			'sx-severe': /\[SEVERE\]/g,
			'sx-col-black': /(§|&)0.+/g,
			'sx-col-dblue': /(§|&)1.+/g,
			'sx-col-dgreen': /(§|&)2.+/g,
			'sx-col-daqua': /(§|&)3.+/g,
			'sx-col-dred': /(§|&)4.+/g,
			'sx-col-dpurple': /(§|&)5.+/g,
			'sx-col-gold': /(§|&)6.+/g,
			'sx-col-gray': /(§|&)7.+/g,
			'sx-col-dgray': /(§|&)8.+/g,
			'sx-col-blue': /(§|&)9.+/g,
			'sx-col-green': /(§|&)a.+/g,
			'sx-col-aqua': /(§|&)b.+/g,
			'sx-col-red': /(§|&)c.+/g,
			'sx-col-lpurple': /(§|&)d.+/g,
			'sx-col-yellow': /(§|&)e.+/g,
			'sx-col-white': /(§|&)f.+/g,
			'sx-col-obfuscated': /(§|&)k.+/g,
			'sx-col-bold': /(§|&)l.+/g,
			'sx-col-strike': /(§|&)m.+/g,
			'sx-col-underline': /(§|&)n.+/g,
			'sx-col-italic': /(§|&)o.+/g,
			'sx-hide': /(§|&)./g,
			'sx-ip-addr': /(?:[0-9]{1,3}\.){3}[0-9]{1,3}/g
		};

		data = $('<div/>').text(response[datapart]).html();

		var selector;
		var replacer = function(m) {
			return '<span class="' + selector + '">' + m + '</span>';
		};

		for (selector in rules) {
			data = data.replace(rules[selector], replacer);
		}

		out = '<div class="console-row">' + data.replace(/\n|\r/g, '</div><div class="console-row">') + '</div>';
		
		$('#console').html(out);
		set_data(data);
	};
});