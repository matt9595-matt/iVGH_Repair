# iVGH Repair API Manager
## iVGHRepair_Stat
### Class RepairTracker
1. Repair_Status_Form() - Repair Number Input Form
    * Prints the HTML form to retrieve repair number from the user
2. GSX_ID() - Monitors Submit Button To Begin API Functions
    * Checks for submit button being pressed
    * Once Pressed begins the 'fetch' function
3. GSX_Fetch() - Function Manager for Repair Tracker Class
    * Retrieves input data from form 
    * Trims input
    * Calls the below functions in order
4. get_auth() - Opens Database for Dynamically Assigned Login Credentials
    * Opens a connection to database with provided variable declarations
    * Calls database entry that holds credential information for api
    * Assigns credential information to private class variables
    * Closes database
5. GSXLogin() - Renew API Key for Call and Store in Database
    * Initializes curl call and creates array to send for post API
    * Execute and curl call and assign API data to private variables
    * Close curl call and store new API key in database
6. RepairCheck() - API to Retrieve Status of Repair and Print it to Webpage
    * Initalize curl and dynamically assign API URL call for curl call
    * Execute curl command store data in private variables and close curl call
    * Check HTTP code from Curl call and email to sysadmin if anything but success
    * If HTTP code is successful proceed to PrintRepair function
7. PrintRepair() - Takes Repair Status Code and Assigns it to User Readable Output
    * Prints default header "thanks for choosing VGH..."
    * Switch statement for repair code
    * Repair code determines the output of repair status print
    * Any unusual repair codes will print to user to call store for status
8. GSXLogout() - Expires the current API key
    * Initialize curl call and create array for post data
    * Executes curl command and closes curl command
## iVGHRepair
1. GSXResults() - Initializes Repairtracker Class and Sets Shortcode Operation for Wordpress
    * Call file with RepairTracker class
    * Initialize class and call two public function to start API call
2. add_shortcode() - Wordpress operation to assign keyword to function 
## REDB.ini
1. No Functions Just an Initialize Script for Credentials Database
    * Readlines for sysadmin to enter the following credentials
    * AppleID - Authorized technician credentials to access API
    * AuthKey - Authorization key provided by apple
    * CertKey - Password for local Apple API certificates
    * Confirm to sysadmin that credentials are received
## VGHMailer
### Class VGHMailer
1. VGHAuthError() - For Error 400 HTTP Codes Resulting in Failed Authentication
    * Print 400 error code into subject line
    * Print instructions to fix error into the body of function
    * Use WPMail integrated functions to email sysadmin
2. VGHOtherError() - Any Other HTTP Error Code Transfered by Parameters
    * Print HTTP code into subject 
    * Print Message of needed maintenance to API manager to sysadmin in body
    * Use WPMail integrated function to email sysadmin
## Notes
1. GSX login and logout functions were moved into a daemon script for linux cronjob. this increased flexibility to enable other API managers withot concern for overlapping curl calls
2. To use the API's listed you must get permission from apple (See GSX Documentation)
3. This API was used for a website created by wordpress and hosted on a LAMP (Linux, Apache, MariaDB, PHP) stack. 
