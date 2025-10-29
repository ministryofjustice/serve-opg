import * as fs from 'node:fs'
import * as process from 'node:process'

import * as esbuild from 'esbuild'
import * as sass from 'sass'

/**
 * Build JS and CSS dependencies, and copy GOVUK images and fonts to a distribution directory.
 * See package.json for the commands to run this script.
 *
 * Outputs go to the serve-web/public/build directory (following the pattern established by the old Encore pipeline).
 *
 * The metafile created for the JS is used by the Twig templates to work out the name of the JS file (whose
 * name includes a hash to prevent incorrect caching); after reading the JSON from the metafile, the path
 * to the compiled JS will be cached into the compiled template.
 */
const isProductionBuild = (process.argv[2] === 'production')

fs.mkdirSync('./public/build/javascripts', {recursive: true})
fs.mkdirSync('./public/build/stylesheets/govuk-frontend/dist/govuk/assets/rebrand/images', {recursive: true})
fs.mkdirSync('./public/build/stylesheets/govuk-frontend/dist/govuk/assets/images', {recursive: true})

// JS
let jsBuildConfig = {
    entryPoints: ['./assets/js/app.js'],
    entryNames: '[name]-[hash]',
    bundle: true,
    outdir: './public/build/javascripts/',
    treeShaking: true, // removes unused and unreachable code
    metafile: true, // output a metadata file about the compiled JS
    minify: false,
    sourcemap: true // creates a sourcemap file alongside the JS file
}

if (isProductionBuild) {
    jsBuildConfig['minify'] = true
    jsBuildConfig['sourcemap'] = false
}

// write the metadata file to the build directory, so it can be used to determine the name of the output compiled JS file
const jsResult = esbuild.buildSync(jsBuildConfig)
fs.writeFileSync('./public/build/meta.json', JSON.stringify(jsResult.metafile))

// SASS
const options = {
    loadPaths: ['./assets/css', './node_modules']
}
const cssResult = sass.compile('./assets/css/app.scss', options)
let css = cssResult.css

if (isProductionBuild) {
    const minifyResult = await esbuild.transform(
        css,
        {
            loader: 'css',
            minify: true,
        }
    )

    css = minifyResult.code
}

fs.writeFileSync('./public/build/stylesheets/app.css', css)

// COPY FILES FROM GOVUK, PLUS OUR OWN ICON FILES
fs.cpSync(
    './node_modules/govuk-frontend/dist/govuk/assets/rebrand/images',
    './public/build/stylesheets/govuk-frontend/dist/govuk/assets/rebrand/images',
    {recursive: true}
)

// move the govuk crest image to the correct folder
fs.renameSync(
    './public/build/stylesheets/govuk-frontend/dist/govuk/assets/rebrand/images/govuk-crest.svg',
    './public/build/stylesheets/govuk-frontend/dist/govuk/assets/images/govuk-crest.svg',
)

fs.cpSync(
    './node_modules/govuk-frontend/dist/govuk/assets/fonts',
    './public/build/fonts/',
    {recursive: true}
)
fs.cpSync('./assets/images/icons', './public/build/icons/', {recursive: true})
