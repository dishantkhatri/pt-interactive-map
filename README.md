# WP Interactive Map

## Description

This WordPress plugin enables the embedding of an interactive map on a page that shows article locations. Each article has latitude and longitude fields, and when the user clicks on the map, a popup displays the article title. Clicking on the title in the popup redirects the user to the article's page.

## Features

- Adds custom latitude and longitude fields to a post type.
- Creates a custom REST API route to fetch post details including title, link, latitude, and longitude.
- Integrates a Leaflet map container using a shortcode.
- Adds custom JavaScript to load markers on the map dynamically based on fetched post data.

## Installation

1. Download and install the plugin in your WordPress site.
2. Activate the plugin from the WordPress admin dashboard.
3. The plugin will automatically add custom fields for latitude and longitude to your posts.
4. Use the provided shortcode to insert the interactive map on any page or post.

## Usage

To display the map on a page or post, use the shortcode `[leaflet_map]` where you want the map to appear. 

Example:
[leaflet_map]

This will render the interactive map. Once the map is rendered, the JavaScript will automatically fetch the post data via the custom API route and plot markers on the map.

## Custom Fields

The plugin adds two custom fields to posts:
- **Latitude**: The latitude of the article's location.
- **Longitude**: The longitude of the article's location.

To set the location of an article, navigate to the post editor and fill in these fields under the "Custom Fields" section.

## REST API Route

A custom REST API route is created to return the necessary data for the map markers. It will return a JSON object with the following fields:
- `title`: The post title.
- `link`: The link to the post.
- `latitude`: The latitude of the post's location.
- `longitude`: The longitude of the post's location.

Example API endpoint:
GET /wp-json/wp/v2/posts-with-lat-lng


## JavaScript & Map Integration

- The plugin uses **Leaflet.js** to render the interactive map.
- The map is only initialized if the container element (with the class `.leaflet-map`) is present in the DOM.
- Upon successful data fetch from the custom API, the map is populated with markers corresponding to each post location.
