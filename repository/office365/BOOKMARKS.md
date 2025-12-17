# Microsoft 365 Repository - Folder Bookmarks Feature

## Overview

The folder bookmarks feature allows content developers and users to save frequently accessed folders for quick access, eliminating the need to traverse the folder tree repeatedly.

## Features

- **Bookmark Folders**: Save frequently accessed folders from My OneDrive, Teams, Groups, and Shared folders
- **Recent Folders Tracking**: Automatically tracks the last 20 folders you've visited
- **Quick Access**: Access bookmarked folders directly from the root level of the file picker
- **Easy Management**: Add and remove bookmarks through a dedicated management interface

## Configuration

### Enabling/Disabling Bookmarks

1. Go to **Site Administration → Plugins → Repositories → Microsoft 365**
2. Find the checkbox labeled "Disable Bookmarks folder in file picker"
3. Uncheck to enable bookmarks (default), or check to disable

## Using Bookmarks

### Viewing Bookmarks

When bookmarks are enabled and you have saved bookmarks:

1. Open the file picker and select "Microsoft 365" repository
2. You'll see a "Bookmarks" folder at the root level (appears only if you have bookmarks)
3. Click on "Bookmarks" to see all your saved bookmarked folders
4. Click on any bookmarked folder to navigate directly to it

### Managing Bookmarks

#### Method 1: In-Picker Bookmark Actions (Easiest)

When you navigate to any folder in the Microsoft 365 file picker, you'll see a bookmark action item at the top of the folder contents:

- **☆ Add to Bookmarks**: Click this to bookmark the current folder
- **★ Remove from Bookmarks**: Click this to remove the current folder from bookmarks (appears when folder is already bookmarked)

This is the quickest way to bookmark folders as you browse!

#### Method 2: Using the Bookmark Manager

1. Navigate to `/repository/office365/bookmark_manager.php` in your Moodle installation
2. The page shows two sections:
   - **Bookmarks**: Your saved bookmarks with options to remove them
   - **Recently Visited Folders**: The last 20 folders you visited with options to bookmark them

#### Adding a Bookmark from Recent Folders:
1. Browse folders in the Microsoft 365 file picker as usual
2. Visit the Bookmark Manager page
3. In the "Recently Visited Folders" section, find the folder you want to bookmark
4. Click "Add to Bookmarks" next to that folder

#### Adding a Bookmark Manually (Advanced):
1. Navigate to the folder in the file picker
2. Note the folder's path (e.g., `/my/01ABCDEF123456789` or `/teams/abc-123-def/456xyz`)
3. Visit the Bookmark Manager page
4. Use the "Add to Bookmarks" form at the bottom:
   - Enter a descriptive title (e.g., "Project Documentation")
   - Enter the folder path you noted
   - Click "Add to Bookmarks"

#### Removing a Bookmark:
1. Visit the Bookmark Manager page
2. Find the bookmark you want to remove in the "Bookmarks" section
3. Click "Remove from Bookmarks"

### Method 2: Programmatic Access (Advanced)

You can also manage bookmarks programmatically using the repository API:

```php
// Get repository instance
$repo = repository::get_type_instance('office365');

// Add a bookmark
$repo->add_bookmark('/my/01ABCDEF123456789', 'My Project Files');

// Remove a bookmark
$repo->remove_bookmark('/my/01ABCDEF123456789');

// Get all bookmarks
$bookmarks = $repo->get_bookmarks();
```

## Folder Path Examples

### OneDrive Paths
- Root: `/my/`
- Folder: `/my/{folder-id}` (e.g., `/my/01ABCDEF123456789`)

### Teams Paths
- Teams root: `/teams/`
- Team folder: `/teams/{team-id}` (e.g., `/teams/abc-def-123`)
- Team subfolder: `/teams/{team-id}/{folder-id}` (e.g., `/teams/abc-def-123/456xyz789`)

### Groups (Courses) Paths
- Groups root: `/groups/`
- Course group: `/groups/{course-id}` (e.g., `/groups/42`)
- Course group folder: `/groups/{course-id}/coursegroup/{folder-id}`

### Shared Paths
- Shared root: `/shared/`
- Shared folder: `/shared/{drive-id}/{item-id}`

## Tips and Best Practices

1. **Use Descriptive Titles**: When adding bookmarks, use clear, descriptive titles that will help you identify the folder later
2. **Regular Cleanup**: Periodically review and remove bookmarks you no longer need
3. **Check Recent Folders**: The "Recently Visited Folders" feature makes it easy to bookmark folders you frequently access
4. **Team Collaboration**: Share folder paths with team members so they can bookmark the same folders

## Troubleshooting

### Bookmarks folder doesn't appear
- Ensure bookmarks are enabled in the repository settings
- Make sure you have at least one bookmark saved
- The Bookmarks folder only appears when you have saved bookmarks

### Can't access a bookmarked folder
- The folder may have been deleted or moved
- You may no longer have permission to access the folder
- Remove the bookmark and create a new one if the folder was moved

### Bookmark Manager page not accessible
- Ensure you are logged in to Moodle
- Check that the file exists at `/repository/office365/bookmark_manager.php`
- Contact your Moodle administrator if you need access

## Technical Details

- Bookmarks are stored in Moodle's user preferences system
- Each user has their own set of bookmarks (not shared with other users)
- Maximum of 20 recently visited folders are tracked automatically
- Bookmarks are stored as JSON in the `repository_office365_bookmarks` preference
- Recent paths are stored in the `repository_office365_recent` preference

## Privacy and Security

- Bookmarks are private to each user
- Bookmarks only store folder paths and titles, not file contents
- Folder permissions are still enforced when accessing bookmarked folders
- Bookmarks do not grant additional access rights

## Future Enhancements

Potential future improvements could include:
- Sharing bookmarks with team members
- Organizing bookmarks into categories
- Importing/exporting bookmarks
- Browser extension for easier bookmark management
