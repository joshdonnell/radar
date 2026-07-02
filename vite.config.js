import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import { defineConfig } from "vite-plus";
import { dirname, resolve } from "path";
import { fileURLToPath } from "url";
import { google } from "laravel-vite-plugin/fonts";
import laravel from "laravel-vite-plugin";

const __dirname = dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    fmt: {
        semi: false,
        singleQuote: true,
        htmlWhitespaceSensitivity: "css",
        printWidth: 80,
        tabWidth: 2,
        sortPackageJson: false,
        sortImports: {
            newlinesBetween: false,
            groups: ["builtin", "external", "internal", "parent", "sibling", "index"],
        },
        ignorePatterns: ["resources/dist/**"],
    },
    lint: {
        plugins: ["eslint", "vue", "typescript", "unicorn", "oxc"],
        categories: {
            correctness: "warn",
        },
        ignorePatterns: ["vendor", "node_modules", "public", "resources/dist"],
        options: {
            typeAware: true,
            typeCheck: true,
        },
    },
    resolve: {
        alias: {
            "~": resolve(__dirname, "resources/js"),
        },
    },
    build: {
        outDir: "resources/dist",
        emptyOutDir: true,
        manifest: "manifest.json",
        rollupOptions: {
            input: resolve(__dirname, "resources/js/app.ts"),
            // @vueuse/core ships /* #__PURE__ */ annotations in positions rolldown
            // can't interpret. The warnings are harmless third-party build noise.
            // once upstream fix exists this can be removed
            checks: {
                invalidAnnotation: false,
            },
            output: {
                codeSplitting: false,
            },
        },
    },
    plugins: [
        vue(),
        laravel({
            input: ["resources/css/app.css", "resources/js/app.ts"],
            refresh: true,
            fonts: [
                google("Inter", {
                    alias: "serif",
                    weights: [400, 500, 600, 700],
                    styles: ["normal", "italic"],
                    subsets: ["latin"],
                    display: "swap",
                    preload: [{ weight: 400 }, { weight: 700 }],
                    fallbacks: ["system-ui", "sans-serif"],
                }),
                google("JetBrains Mono", {
                    alias: "mono",
                    weights: [400, 500, 600],
                    subsets: ["latin"],
                    display: "swap",
                    fallbacks: ["ui-monospace", "monospace"],
                }),
            ],
        }),
        tailwindcss(),
    ],
});
