import { defineConfig } from "vite";
import { resolve } from "path";
import liveReload from "vite-plugin-live-reload";
import { viteStaticCopy } from "vite-plugin-static-copy";
import { imageToWebpPlugin } from "vite-plugin-image-to-webp";

const VITE_HOST = process.env.VITE_PUBLIC_HOST || "localhost";
const IS_PRODUCTION = process.env.NODE_ENV === "production";

console.log(VITE_HOST);

export default defineConfig({
  cacheDir: "/tmp/vite-cache",
  base: IS_PRODUCTION
    ? "/wp-content/plugins/tudor/dist/assets/"
    : `https://${VITE_HOST}/`,

  plugins: [
    liveReload([resolve(__dirname, "**/*.php")]),
    viteStaticCopy({
      targets: [{ src: "src/images/*", dest: "images" }],
    }),
    imageToWebpPlugin({
      imageFormats: ["jpg", "jpeg", "png"],
      outputFolder: resolve(__dirname, "dist/assets/images"),
    }),
  ],

  server: {
    host: "0.0.0.0", // obbligatorio in Docker
    port: 5173,
    strictPort: true,
    cors: true,
    allowedHosts: "all",
    watch: {
      usePolling: true,
      interval: 500,
    },
    hmr: {
      host: "vite.menichelli.pixelldemo.com", // dove il browser si connette
      protocol: "wss",
      clientPort: 443, // porta esterna (nginx)
      // NON specificare port qui — altrimenti Vite tenta di bindare quella porta
    },
  },

  build: {
    outDir: resolve(__dirname, "dist/assets"),
    emptyOutDir: true,
    manifest: true,
    assetsDir: "",
    rollupOptions: {
      input: {
        main: resolve(__dirname, "src/sjs/main.js"),
        style: resolve(__dirname, "src/scss/style.scss"),
      },
      output: {
        entryFileNames: "js/[name]-[hash].js",
        chunkFileNames: "js/[name]-[hash].js",
        assetFileNames: (assetInfo) => {
          const ext = assetInfo.name.split(".").pop();
          if (ext === "css") return "css/[name].[hash][extname]";
          if (/png|jpe?g|webp|svg|gif/i.test(ext))
            return "images/[name].[hash][extname]";
          if (/woff2?|eot|ttf|otf/i.test(ext))
            return "fonts/[name].[hash][extname]";
          return "[name].[hash][extname]";
        },
      },
    },
  },
});
