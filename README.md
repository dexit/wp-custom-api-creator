# Custom API Creator

**Contributors:** mehdiraized  
**Tags:** api, rest api, custom api, api builder, wp json  
**Requires at least:** 5.0  
**Tested up to:** 6.6.1  
**Stable tag:** 1.0.4
**Requires PHP:** 7.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Custom API Creator is a WordPress plugin that lets developers create flexible, customize data, and control access with role restrictions.

## Description

Custom API Creator is a powerful WordPress plugin that allows developers and site owners to create custom REST API endpoints with ease. This plugin bridges the gap between your WordPress content and custom applications, enabling you to expose your data in a flexible, secure, and organized manner.

With Custom API Creator, you can define multiple API endpoints, each with its own set of data sections. Choose which post types and fields to include, control access with user role restrictions, and customize the structure of your API responses. Whether you're building a mobile app, integrating with a third-party service, or creating a headless WordPress setup, Custom API Creator provides the tools you need to shape your data output.

### Features

- **Easy-to-Use Interface:** Create and manage custom API endpoints directly from the WordPress admin panel.
- **Multiple Data Sections:** Define multiple sections within a single API endpoint, each with its own post type and field selection.
- **Custom Section Names:** Assign meaningful names to each section for clear and organized API responses.
- **Flexible Field Selection:** Choose which fields to include for each post type, including title, content, excerpt, categories, and tags.
- **Taxonomy Integration:** Include data from custom taxonomies in your API responses.'
- **Access Control:** Set endpoints as public or restrict access to specific user roles.
- **Dynamic Endpoints:** Create endpoints with parameters for flexible data retrieval.
- **Automatic REST API Integration:** Your custom APIs are automatically integrated into the WordPress REST API system.
- **Performance Optimized:** Efficiently retrieves and formats data to ensure fast API responses.
- **Developer Friendly:** Clean code structure and hooks for easy customization and extension.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/custom-api-creator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.

## Frequently Asked Questions

### How do I create a new custom API endpoint?

1. In your WordPress admin panel, go to "Custom API Creator" in the main menu.
2. Click "Add New" to create a new API endpoint.
3. Set your desired endpoint URL, add data sections, choose post types and fields, and set access permissions.
4. Publish your API, and it's ready to use!

### Can I include custom post types in my API?

Yes! Custom API Creator works with any public post type, including custom post types created by other plugins or themes.

### How do I restrict access to my API?

When creating or editing an API, you can choose between public access or restricted access. If you select restricted access, you can then specify which user roles are allowed to access the API.

### Can I include custom fields in my API response?

The current version focuses on core WordPress fields (title, content, excerpt) and taxonomies. Support for custom fields is planned for a future update.

### How do I use parameters in my API endpoint?

You can include parameters in your endpoint URL using square brackets, like this: `my-api/[parameter]`. These parameters can then be used to filter your API results.

### Is this plugin compatible with caching plugins?

Yes, Custom API Creator is compatible with most caching plugins. However, for dynamic APIs that change frequently, you may need to configure your caching plugin to exclude your API endpoints.

### Can I modify the JSON structure of the API response?

The plugin provides a standardized JSON structure based on your configured sections and fields. For advanced customization of the JSON structure, you may need to use WordPress filters to modify the response.

### Is Custom API Creator compatible with WordPress multisite?

Yes, Custom API Creator is fully compatible with WordPress multisite installations.

### How can I debug if my API is not working as expected?

Check your endpoint URL, ensure your access settings are correct, and verify that your selected post types have published content. You can also check your server's error logs for any PHP errors.

### Does this plugin support authentication for private APIs?

Custom API Creator uses WordPress's built-in authentication system. For restricted APIs, users must be logged in and have the appropriate role to access the API.

### Can I use this plugin to create APIs for e-commerce data?

While the plugin doesn't have built-in e-commerce fields, you can use it with e-commerce post types. For deeper integration with specific e-commerce plugins, custom development may be required.

### How often is the plugin updated?

We strive to keep Custom API Creator up-to-date with the latest WordPress versions and security best practices. Updates are released as needed for bug fixes, security patches, and new features.

## Changelog

### 1.0.1 Stable version

- Bug fix.

## 1.0.0 First version

- Initial release.

## Support and Feedback

For support and feedback, please visit our [support page](https://mehd.ir). We value your feedback and suggestions for improving the plugin.

## Donate

If you find this plugin useful, please consider supporting its development by [buying me a coffee](https://www.buymeacoffee.com/mehdiraized). Your support helps cover the costs of maintaining and improving the plugin, ensuring it remains free and accessible for everyone. Thank you!

## License

This plugin is licensed under the GPLv2 or later. For more details, visit [GPL License](https://www.gnu.org/licenses/gpl-2.0.html).
