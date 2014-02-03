# Multicraft

This repository contains a Multicraft panel with significant improvements and adjustments over the original Multicraft panel, namely a complete port to Bootstrap and a sane build system.

Until stated otherwise, content in this repository is proprietary and may be not used or distributed outside except for in evaluative purposes, by parties other than xhost.ch.

##Installation
You can install the panel from the files in the `panel.zip` file, or by compiling the panel yourself, from source.

###Compiling from Source
You will first need the [Node Package Manager](https://npmjs.org/) installed on your system. 

Then, you will need Twitter's  [Bower](http://bower.io/) and [GruntJS](http://gruntjs.com/) CLI installed globally. These can be installed by running:

```
    sudo npm install -g bower && npm install -g grunt-cli
```

You will then need to install the dependencies to build the panel (including the LESS compiler, Uglify, Imagemin, and so on) as well as the assets it depends upon (Bootstrap, FontAwesome, and the jQuery Knob). To install the dependencies, simply run the following command while `cd`'d in the project directory:

```
   bower install && npm install
```

After than, you can use Grunt to compile assets according to your purpose:

- `grunt` - Compiles LESS and copies scripts and images, without any minfication
- `grunt spy` - Watches for changes and recompiles in realtime when styles or scripts are updated.
- `grunt dist` - Builds and minifies scripts and images. This may take a minute or two, depending on the system.
- `grunt pack` - Clears the state of panel (wipes configs and runtime files) and zips it into panel.zip.

Aside from these, you may also find it useful to manually clear the state of the panel, triggering a rerun of the installation procedure. You can do this by running `grunt clean:state`.

##Customization

Styles may be customized very easily, simply by changing [LESS](lesscss.org) variables in `static/css/style.less` and recompiling. A number of theme-specific variables are there, and, as Bootstrap is built in LESS as well, you can override the default LESS settings. By inserting any variable that appears in [Bootstrap's variables.less file](https://github.com/twbs/bootstrap/blob/master/less/variables.less) into this panel's style.less, you can override and change Bootstrap's default appearance.