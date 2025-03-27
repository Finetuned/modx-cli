# Why this project exists
modx-cli exists to enable programmatic and automated control of the MODX content management framework version 3. Source code for MODX is stored at https://github.com/modxcms/revolution

## Problems it solves

MODX-CLI is a command-line interface for WordPress that solves several problems, making MODX management more efficient and automatable. Here are the key problems it addresses:

### **1\. Time-Consuming Manual Tasks**

* Manually updating MODX core, elements, and extras through the dashboard is slow.  
* MODX-CLI allows bulk updates with simple commands, reducing time and effort.

### **2\. Lack of Automation**

* Routine tasks like creating users, installing extra, and configuring settings require manual clicks.  
* MODX-CLI enables scripting and automation for these processes, making it ideal for CI/CD workflows.

### **3\. Limited Access to GUI**

* In some environments (e.g., headless servers, remote servers, or broken sites), accessing the MODX manager isn't possible.  
* MODX-CLI provides full control over MODX via the command line.

### **4\. Slow Database Management**

* Exporting, importing, searching, and replacing database values through phpMyAdmin can be slow and error-prone.  
* MODX-CLI allows fast and efficient database management (`modx db export`, modx `search-replace`, etc.).

### **5\. Debugging and Maintenance**

* Debugging through logs or trial and error in the MODX manager is inefficient.  
* MODX-CLI provides tools for debugging (`modx debug`), checking site health, and even resetting installations.

### **6\. Bulk Operations**

* Performing actions like user management, resource deletion, or media cleanup via the GUI is tedious.  
* MODX-CLI allows bulk operations with simple commands (e.g., `modx resource delete $(modx resource list --post_type='revision' --format=ids)`).

### **7\. Development Workflow Bottlenecks**

* Installing and setting up MODX locally for development takes time.  
* MODX-CLI streamlines local development with commands like `modx core download`, `modx config create`, and `modx db create`.

Overall, MODX-CLI is a powerful tool for developers, sysadmins, and anyone managing MODX sites, making workflows faster, more reliable, and easily automated.



# How it should work

MODX-XLI should follow the Command Line Interface Guidelines as set out in [https://clig.dev](https://clig.dev/) and it should work in a similar fashion to WP-CLI for Wordpress as stored in the project repository at [https://wp-cli.org](https://github.com/wp-cli/wp-cli). 

# User experience goals

## The UX (User Experience) goals for MODX-CLI focus on efficiency, simplicity, and automation to improve M ODX management for developers and sysadmins. Here are the key UX goals:

### **1\. Speed and Efficiency**

* Reduce the time spent on repetitive tasks (e.g., updates, installations, backups).  
* Provide instant feedback on commands with minimal processing overhead.

### **2\. Simplicity and Consistency**

* Use a clear, predictable command structure (`modx <command> <subcommand> [options]`).  
* Maintain consistency across commands to reduce the learning curve.

### **3\. Automation and Scripting Support**

* Enable users to integrate MODX-CLI into shell scripts, cron jobs, and CI/CD pipelines.  
* Provide non-interactive mode options for fully automated workflows.

### **4\. Minimal Dependencies and Compatibility**

* Work across different hosting environments, including shared hosting and headless servers.  
* Ensure compatibility with various MODX version 3+ and configurations.

### **5\. Reliability and Debugging Support**

* Provide clear error messages and logging options.  
* Include built-in debugging tools (`modx debug`,  `modx doctor`) for troubleshooting.

### **6\. User Control and Transparency**

* Allow users to see exactly what each command does before execution (`--dry-run`).  
* Avoid unexpected changes, ensuring explicit user control over operations.

### **7\. Extensibility**

* Allow developers to extend MODX-CLI with custom commands and plugins.  
* Support for third-party integrations to enhance functionality.

By meeting these UX goals, MODX-CLI provides a powerful, user-friendly experience tailored to MODX developers and administrators, making site management more intuitive and efficient.

