Moodle Plugin - User-IP mapping quiz access rule

Need ->

1) If we want to restrict a user to attempt the quiz only from a pre-allocated location(e.g IP address).
2) To have a record of from which all locations the quiz was attempted and by whom.

Solution ->

Users appearing for the quiz need to be mapped with an IP address.

Installation ->

1) Download and unzip the zip file.
2) Place the plugin folder in the " /mod/quiz/accessrule " subdirectory.
3) Remove the accessrule_navigation_patch.txt file from the " /mod/quiz/accessrule/useripmapping " subdirectory and place it under " /mod/quiz/ ".
4) Apply the patch by 

   i) navigating to the folder
  ii) executing following command in the command prompt
       patch -p3 < accessrule_navigation_patch.txt
       
5) Visit http://yoursite.com/admin to finish the installation
6) Complete the installation by clicking on “Upgrade Moodle database now”,click on continue after the success 
   notification appears on the page.

Usage ->

To use this plugin,follow below two steps.

1) Enable "Enable user-IP mapping",in Quiz Settings(it can be done while creating the quiz
   or after creating,by visiting Edit Settings)
2) Check "Allow Unmapped" if you want to allow the unmapped users to attempt the quiz.If left unchecked,unmapped users 
   would not be allowed to attempt the quiz.
3) User-IP mapping can be managed in the quiz administration block.
   Quiz administration block->Access Rule->User-IP Mapping->Import user-IP mappings.
4) User-IP mappings are quiz dependent.For every quiz you wish to use this plugin,a mapping list should be uploaded again from
   their respective quiz administration block.
5) A quiz can have multiple mapping uploaded,though only the latest mapping would be considered to restrict the student from
   attempting the quiz.

Requirement of the mapping file to be uploaded are:

1) The file should be in CSV(comma separated values) format.
2) It should have two fields.First field should be for usernames and second for IP addresses.
3) Both the fields(username,ip) are required field,if even any one of the field is missing would lead to an error and the upload 
   wouldn't proceed untill all the required fields are present in the csv file.
4) There should be one to one mapping between user and IP address,i.e each user should be mapped to only one IP address 
   (in multiple mapping uploads,previous alloted IP would override with the latest one by default.)


