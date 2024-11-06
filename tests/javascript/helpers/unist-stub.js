// Unit tests failed after upgrading nextcloud-vue v7 to v8
// jest uses commonjs, but the app esm
// use this stub to ignore these errors for the included unist-util-visit and unist-builder

//  ● Test suite failed to run
//
//    Jest encountered an unexpected token
//
//    Jest failed to parse a file. This happens e.g. when your code or its dependencies use non-standard JavaScript syntax, or when Jest is not configured to support such syntax.
//
//    Out of the box Jest supports Babel, which will be used to transform your files into valid JS based on your Babel configuration.
//
//    By default "node_modules" folder is ignored by transformers.
//
//    Here's what you can do:
//     • If you are trying to use ECMAScript Modules, see https://jestjs.io/docs/ecmascript-modules for how to enable it.
//     • If you are trying to use TypeScript, see https://jestjs.io/docs/getting-started#using-typescript
//     • To have some of your "node_modules" files transformed, you can specify a custom "transformIgnorePatterns" in your config.
//     • If you need a custom transformation specify a "transform" option in your config.
//     • If you simply want to mock your non-JS modules (e.g. binary assets) you can stub them out with the "moduleNameMapper" config option.
//
//    You'll find more details and examples of these config options in the docs:
//    https://jestjs.io/docs/configuration
//    For information about custom transformations, see:
//    https://jestjs.io/docs/code-transformation
//
//    Details:
//
//    /home/build/nextcloud/apps/news/node_modules/@nextcloud/vue/node_modules/unist-util-visit/index.js:2
//    export {CONTINUE, EXIT, SKIP, visit} from './lib/index.js'
//    ^^^^^^
//
//    SyntaxError: Unexpected token 'export'
//
//      28 | import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
//      29 | import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
//    > 30 | import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'
//         | ^
//      31 |
//      32 | import { Folder } from '../types/Folder'
//      33 | import { ACTIONS } from '../store'
//
//      at Runtime.createScriptFromCode (node_modules/jest-runtime/build/index.js:1505:14)
//      at Object.<anonymous> (node_modules/@nextcloud/vue/dist/chunks/autolink-BAgL31EZ.cjs:2:24)
//      at Object.<anonymous> (node_modules/@nextcloud/vue/dist/chunks/NcAvatar-B238cv9d.cjs:27:18)
//      at Object.<anonymous> (node_modules/@nextcloud/vue/dist/chunks/NcListItemIcon-Do_af_2v.cjs:4:18)
//      at Object.<anonymous> (node_modules/@nextcloud/vue/dist/chunks/NcSelect-CKgkjF4m.cjs:10:24)
//      at Object.<anonymous> (node_modules/@nextcloud/vue/dist/Components/NcSelect.cjs:2:18)
//      at Object.<anonymous> (src/components/MoveFeed.vue:30:1)
//      at Object.<anonymous> (src/components/SidebarFeedLinkActions.vue:118:1)
//      at Object.<anonymous> (tests/javascript/unit/components/SidebarFeedLinkActions.spec.ts:4:1)

//  ● Test suite failed to run
//
//    Jest encountered an unexpected token
//
//    Jest failed to parse a file. This happens e.g. when your code or its dependencies use non-standard JavaScript syntax, or when Jest is not configured to support such syntax.
//
//    Out of the box Jest supports Babel, which will be used to transform your files into valid JS based on your Babel configuration.
//
//    By default "node_modules" folder is ignored by transformers.
//
//    Here's what you can do:
//     • If you are trying to use ECMAScript Modules, see https://jestjs.io/docs/ecmascript-modules for how to enable it.
//     • If you are trying to use TypeScript, see https://jestjs.io/docs/getting-started#using-typescript
//     • To have some of your "node_modules" files transformed, you can specify a custom "transformIgnorePatterns" in your config.
//     • If you need a custom transformation specify a "transform" option in your config.
//     • If you simply want to mock your non-JS modules (e.g. binary assets) you can stub them out with the "moduleNameMapper" config option.
//
//    You'll find more details and examples of these config options in the docs:
//    https://jestjs.io/docs/configuration
//    For information about custom transformations, see:
//    https://jestjs.io/docs/code-transformation
//
//    Details:
//
//    /home/build/nextcloud/apps/news/node_modules/unist-builder/index.js:6
//    export {u} from './lib/index.js'
//    ^^^^^^
//
//    SyntaxError: Unexpected token 'export'
//
//      103 | import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
//      104 | import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
//    > 105 | import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'
//          | ^
//      106 |
//      107 | import { Folder } from '../types/Folder'
//      108 | import { ACTIONS } from '../store'
//
//      at Runtime.createScriptFromCode (node_modules/jest-runtime/build/index.js:1505:14)
//      at Object.<anonymous> (node_modules/@nextcloud/vue/dist/chunks/autolink-BAgL31EZ.cjs:3:22)
//      at Object.<anonymous> (node_modules/@nextcloud/vue/dist/chunks/NcAvatar-B238cv9d.cjs:27:18)
//      at Object.<anonymous> (node_modules/@nextcloud/vue/dist/chunks/NcListItemIcon-Do_af_2v.cjs:4:18)
//      at Object.<anonymous> (node_modules/@nextcloud/vue/dist/chunks/NcSelect-CKgkjF4m.cjs:10:24)
//      at Object.<anonymous> (node_modules/@nextcloud/vue/dist/Components/NcSelect.cjs:2:18)
//      at Object.<anonymous> (src/components/AddFeed.vue:105:1)
//      at Object.<anonymous> (src/components/routes/Explore.vue:47:1)
//      at Object.<anonymous> (src/routes/index.ts:3:1)
//      at Object.<anonymous> (src/components/Sidebar.vue:188:1)
//      at Object.<anonymous> (src/App.vue:51:1)
//      at Object.<anonymous> (tests/javascript/unit/components/App.spec.ts:3:1)
