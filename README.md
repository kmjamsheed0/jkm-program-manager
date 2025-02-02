# JKM Programs Manager

## Overview
JKM Programs Manager is a lightweight WordPress plugin that allows you to manage and list programs using a custom post type (**CPT**) called `jkm_programs`. The plugin supports additional metadata to store:
- **Program period range** (start and end dates)
- **Broadcast schedule** (JSON format with weekly days and time)

You can list the created programs on the frontend using the shortcode:

```
[JKMPM_PROGRAMS]
```

## Installation
You can download the **tagged version** from the repository **tags section** or **clone** the repo to extend functionality.

## Importing Programs via CSV
To bulk import programs, use a CSV file formatted as follows:

### **CSV File Example**

#### **Columns**
- Program Name
- Program Description
- Program Start Date
- Program End Date
- Program Thumbnail (Public URL with file extension)
- Broadcast Schedule (JSON format with 24-hour time format)

#### **Sample Data**
```
Program Name,Program Description,Program Start Date,Program End Date,Program Thumbnail,Broadcast Schedule
Morning News,Daily news updates,2025-01-01,2025-01-31,https://example.com/image1.jpg,"{""Mon"": ""08:00"", ""Tue"": ""09:00"", ""Wed"": ""10:00""}"
```

### **Important Notes:**
- **Broadcast Schedule** should be in **JSON format** with **24-hour time format** (e.g., `16:00` for 4:00 PM).
- **Thumbnail URLs** must be **valid, publicly accessible URLs** ending with a proper **file extension** (e.g., `.jpg`, `.png`).

## Usage
1. **Create or import programs** using the plugin’s admin interface.
2. **Use the `[JKMPM_PROGRAMS]` shortcode** to display the programs on the frontend.
3. **Ensure correct CSV formatting** for importing bulk programs.

