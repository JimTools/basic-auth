import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  locales: {
    root: {
      label: 'English',
      lang: 'en'
    },
  },
  srcDir: "docs",

  title: "JimTools: basic-auth",
  description: "Docs for jimtools/basic auth",
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    nav: [],

    sidebar: [
      {
        text: 'Table Of Contents',
        items: [
          { text: 'Overview', link: '/overview' },
          { text: 'Quickstart', link: '/quickstart' },
          { text: 'Optional Parameters', link: '/parameters' },
          { text: 'Security', link: '/security' },
          { text: 'Custom Authentication Methods', link: '/customise' },
        ]
      }
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/jimtools/basic-auth' }
    ]
  }
})
