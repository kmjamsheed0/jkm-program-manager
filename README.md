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

- Missed to include **an activation redirect** and **settings link** function that helps navigate to the plugin's menu page.
- The plugin settings can be found in the **WordPress dashboard** under the menu **'Radio Programs'**, which has an **audio playlist icon**.
- To access the plugin settings, please **navigate to the 'Radio Programs'** menu in the WordPress dashboard.

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
Tech Talk,"Latest discussions on technology trends",2025-02-01,2025-02-28,https://picsum.photos/id/10/200/300.jpg,"{""Tue"": ""14:00"", ""Thu"": ""15:30""}"
Health Hour,"Tips and advice on healthy living",2025-04-01,2025-04-30,https://picsum.photos/id/10/200/300.jpg,"{""Mon"": ""10:00"", ""Wed"": ""11:00"", ""Fri"": ""12:30""}"
Music Mania,"A show featuring the best of classic and modern music",2025-05-01,2025-05-31,https://picsum.photos/id/10/200/300.jpg,"{""Sat"": ""17:00"", ""Sun"": ""18:00""}"
Travel Diaries,"Exploring the world's best destinations",2025-06-01,2025-06-30,https://picsum.photos/id/10/200/300.jpg,"{""Wed"": ""15:00"", ""Sun"": ""16:30""}"
Food Fiesta,"A show about delicious recipes and food culture",2025-07-01,2025-07-31,https://picsum.photos/id/10/200/300.jpg,"{""Fri"": ""12:00"", ""Sat"": ""13:30""}"
```

### **Important Notes:**
- **Broadcast Schedule** should be in **JSON format** with **24-hour time format** (e.g., `16:00` for 4:00 PM).
- **Thumbnail URLs** must be **valid, publicly accessible URLs** ending with a proper **file extension** (e.g., `.jpg`, `.png`).

## Usage
1. **Create or import programs** using the plugin’s admin interface.
2. **Use the `[JKMPM_PROGRAMS]` shortcode** to display the programs on the frontend.
3. **Ensure correct CSV formatting** for importing bulk programs.

