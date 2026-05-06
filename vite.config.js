import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import { defineConfig } from "vite-plus";
import { dirname, resolve } from "path";
import { fileURLToPath } from "url";

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
        ignorePatterns: [
            "vendor",
            "node_modules",
            "public",
            "resources/dist",
        ],
        options: {
            typeAware: true,
            typeCheck: true,
        },
    },
    resolve: {
        alias: {
            "@": resolve(__dirname, "resources/js"),
        },
    },
    build: {
        outDir: "resources/dist",
        emptyOutDir: true,
        manifest: "manifest.json",
        rollupOptions: {
            input: resolve(__dirname, "resources/js/app.ts"),
            output: {
                codeSplitting: false,
            },
        },
    },
    plugins: [vue(), tailwindcss()],
});
