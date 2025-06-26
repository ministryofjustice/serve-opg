import * as fs from 'node:fs'
import * as process from 'node:process'

import * as esbuild from 'esbuild'
import * as sass from 'sass'

const isProductionBuild = (process.argv[2] === 'production')

// JS
let jsBuildConfig = {
    entryPoints: ['./assets/js/app.js'],
    entryNames: '[name]-[hash]',
    bundle: true,
    outdir: './public/build/',
    treeShaking: true,
    metafile: true,
    minify: false,
    sourcemap: true
}

if (isProductionBuild) {
    jsBuildConfig['minify'] = true
    jsBuildConfig['sourcemap'] = false
}

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

fs.writeFileSync('./public/build/app.css', css)

// COPY FILES
fs.cpSync('./node_modules/govuk-frontend/dist/govuk/assets/images', './public/build/images/', {recursive: true})
fs.cpSync('./node_modules/govuk-frontend/dist/govuk/assets/fonts', './public/build/fonts/', {recursive: true})
fs.cpSync('./assets/images/icons', './public/build/icons/', {recursive: true})
