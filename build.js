var sys = require('sys'),
    util = require('util'),
    child_process = require('child_process'),
    fs = require('fs'),
    wrench = require('./wrench');

process.stdin.setEncoding('utf8');

var exec = function (command, cb) {
    child_process.exec(command, {
        cwd: __dirname
    }, cb);
}

var promptUser = function (message, options, callback, def) {
    process.stdin.resume();
    for (var i in options) {
        console.log(util.format('    [%s] %s', i, options[i]));
    }

    var givePrompt;
    (givePrompt = function() {
        var str = message;
        if (def) {
            str += ' [' + def + ']';
        }
        console.log('\n' + str);
    })();

    process.stdin.on('data', function (data) {
        data = data.trim();
        if (typeof message[data] === 'undefined') {
            console.log('Invalid option selected! If you\'d like to cancel this, press Control+C');
            givePrompt();
            return false;
        }
        callback(data);
        process.stdin.removeAllListeners('data');
    });
};

var askTheme, askAction,
    themes = fs.readdirSync('themes'),
    choices = {},
    steps = [];

steps.push(function(cb) {
    promptUser('Enter the number of the theme you\'d like to build, and hit Enter.', themes, function(choice) {
        choices.theme = themes[choice];
        cb();
    });
});

steps.push(function(cb) {
    if (!fs.existsSync('node_modules')) {
        console.log('Installing build prerequisites. This only needs to be done once, and will take several minutes. Go grab a coffee while you wait!');
        exec('npm install', function (err, stdout, stderr) {
            if (err) {
                return console.log('Error! Please be sure npm is installed and available on your system!');
            }
            cb();
        });
    } else {
        cb();
    }
});

steps.push(function(cb) {
    if (!fs.existsSync('bower_components')) {
        console.log('Installing static asset sources, this should only take a few moments.');
        exec('node node_modules/bower/bin/bower install --allow-root', function (err, stdout, stderr) {
            if (err) {
                return console.log('Error! Please be sure npm is installed and available on your system!');
            }
        });
        cb();
    } else {
        cb();
    }
});

steps.push(function(cb) {
    wrench.copyDirSyncRecursive('themes/' + choices.theme, 'static', {
        forceDelete: true
    });

    cb();
});

steps.push(function(cb) {
    console.log('Compiling all assets. This may take a minute depending on the speed of your system.');
    exec('node node_modules/grunt-cli/bin/grunt pack', cb);
});

steps.push(function(cb) {
    console.log('Cleaning up. Your panel is in "panel.zip" and is ready for deployment!');
    wrench.rmdirSyncRecursive('static');
    cb();
});

var run;
(run = function() {
    func = steps.shift();
    if (func) {
        func(run);
    } else {
        process.exit();
    }
})();