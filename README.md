# CP Helper for Double Secret Agency

**Internal tools for the Craft CMS control panel.**

---

This private plugin is an internal tool for Double Secret Agency websites. It adds a few niceties which improve the DX of the control panel.

## CP Enhancements

**Hides the "Dashboard" link from the main navigation.** This mimics the behavior of the [Dashboard Begone](https://plugins.craftcms.com/dashboard-begone) plugin.

**Hides the "Utilities" badge,** which is often distracting and extraneous.

**Hides the "All Entries" link from the secondary navigation.** Users will be redirected to the first available section.

**Shows the total number of elements in each section.** For example, how many entries exist in each channel.

## Global Twig Variables

This plugin automatically sets two additional global Twig variables...

| Variable       | Value                           | Path To                    |
|:---------------|:--------------------------------|:---------------------------|
| `resourcesUrl` | `https://example.com/resources` | Front-end CSS & JS files   |
| `assetsUrl`    | `https://example.com/assets`    | Uploaded files for content |
