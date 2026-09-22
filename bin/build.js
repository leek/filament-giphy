import * as esbuild from 'esbuild'

const context = await esbuild.context({
    define: {
        'process.env.NODE_ENV': `'production'`,
    },
    bundle: true,
    format: 'esm',
    mainFields: ['module', 'main'],
    platform: 'neutral',
    sourcemap: false,
    sourcesContent: false,
    treeShaking: true,
    target: ['es2020'],
    minify: true,
    entryPoints: ['./resources/js/filament/rich-content-plugins/giphy.js'],
    outfile: './resources/js/dist/filament/rich-content-plugins/giphy.js',
})

await context.rebuild()
await context.dispose()
