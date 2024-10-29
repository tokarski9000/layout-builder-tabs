# Layout Builder Tabs Module

## Overview

The Layout Builder Tabs module provides a flexible way to add tabbed layouts to your Drupal site. It includes configuration options for tab titles, colors, icons, and container usage. The module also includes JavaScript functionality to handle tab overlap styling.

## Features

- Configure tab titles, colors, and icons.
- Option to use a container for each tab.
- JavaScript to handle tab overlap styling.

## Installation

1. Download and place the module in the `modules/custom` directory.
2. Enable the module using Drush or the Drupal admin interface:
   ```sh
   drush en layout_builder_tabs
   ```

## Configuration

1. Navigate to the module configuration page.
2. Add the desired number of tabs.
3. Configure each tab's title, colors, and icon.
4. Save the configuration.

## Usage

1. Add the Layout Builder Tabs block to your layout.
2. Configure the block settings as needed.

## JavaScript Behavior

The module includes a JavaScript file `layout-tabs-script.js` that handles the overlap styling for the tabs. This script initializes the `LayoutTabs` class and sets the overlap height based on the navigation height.

## Hooks

### `layout_builder_tabs_theme()`

Defines the theme hooks used by the module.

### `layout_builder_tabs_theme_suggestions_alter()`

Alters theme suggestions for the `layout_tabs_section` hook.

## License

This project is licensed under the GPL-2.0-or-later license. See the `LICENSE` file for more details.
