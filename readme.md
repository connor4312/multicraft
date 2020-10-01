# use this!

Multicraft 2.0, which is based upon this theme has officially entered prerelease. You can [download it here](http://multicraft.org/site/page?view=news) - it fixes a couple bugs which remained in this repo, and includes a precompiled theme for you.

Contact [their support](http://multicraft.org/site/contact) if you have any questions ;)

# Multicraft

This repository contains a Multicraft panel with significant improvements and adjustments over the original Multicraft panel, namely a complete port to Bootstrap and a sane build system.

##Installation
The build process is fairly straightforward. First, you'll need [Node.js](http://nodejs.org/download/) installed on your system. Then, run `node build.js`. The script will ask you for the theme you want to install (from the `themes` directory), and complete the compilation process from there by itself. It will put the compiled panel into `panel.zip`, which is then ready to be run on your web server.

##Development
The installation script effectively does the following:

  1. Prompt for the theme to copy from the `themes` directory. This is then copied into the folder `static`.
  2. Ensure that dependencies are met on NPM, which includes the CLI Grunt and Bower tools.
  3. Ensure that dependencies are met in Bower.
  4. Runs "grunt pack" to build assets and create the zip file.
  5. Removes the "static" folder.

For development, you'll probably only want to work with one theme in your own environment, rather than using the included script. So, let's get started. First copy the theme you want to base *your* theme off of manually into the `static` directory, such that paths like `static/css` and `static/img` exist.

Then, you will want Twitter's  [Bower](http://bower.io/) and [GruntJS](http://gruntjs.com/) CLI installed globally. These can be installed by running:

```
    sudo npm install -g bower && npm install -g grunt-cli
```

You will then need to install the dependencies to build the panel (including the LESS compiler, Uglify, Imagemin, and so on) as well as the assets it depends upon (Bootstrap, FontAwesome, and the jQuery Knob). To install the dependencies, simply run the following command while `cd`'d in the project directory:

```
   bower install --allow-root && npm install
```

After than, you can use Grunt to compile assets according to your purpose:

- `grunt` - Compiles LESS and copies scripts and images, without any minfication
- `grunt spy` - Watches for changes and recompiles in realtime when styles or scripts are updated.
- `grunt dist` - Builds and minifies scripts and images. This may take a minute or two, depending on the system.
- `grunt pack` - Clears the state of panel (wipes configs and runtime files) and zips it into panel.zip.

Aside from these, you may also find it useful to manually clear the state of the panel, triggering a rerun of the installation procedure. You can do this by running `grunt clean:state`.

###Customization

Styles may be customized very easily, simply by changing [LESS](lesscss.org) variables in `static/css/style.less` and recompiling. A number of theme-specific variables are there, and, as Bootstrap is built in LESS as well, you can override the default LESS settings. By inserting any variable that appears in [Bootstrap's variables.less file](https://github.com/twbs/bootstrap/blob/master/less/variables.less) into this panel's style.less, you can override and change Bootstrap's default appearance.


## License

This is subject to the same terms and conditions as Multicraft is, and is copyright by xhost.ch. Other than that:

```
            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE 
   TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION 

  0. You just DO WHAT THE FUCK YOU WANT TO.
  
  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
  ```
