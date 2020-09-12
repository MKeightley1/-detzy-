## <<Project Name>>

### Installation 

Install laravel composer
```
composer install
```
<img src="./myimage.jpg">
Install npm modules
```
npm install
```

Create `.env`

```
cp .env.example .env
```


### Additional Front-end work

### How to use
Once both node and php servers are running - navigate to the url: http://127.0.0.1:8000/
1. A dropdown meny should appear with a empty table set.
2. Select a city and the corresponding weather forecast should appear.

### Design/ Decisions made
1. Added code to the app.js for react capability by create a parent and child class relationship. Maybe in future break this down further, build units and follow the framework.
2. Currently only log errors in console - might be a better way to handle this expecially for API issues.
3. Attempted to use tailwind but was not sure how far to go with css. Might need to add more to the dropdown in future.
4. Should have seperated the fetch out of the component so it could be re-used through out code.
5. Components are very specific - would have like to make them more generic and re-usable.
6. Noticed the format of the table data is different to my format. Will need to make this consisant with design.

### Limitations/issues
1. Have hardcoded list of Australian cities for demonstrate
2. A bug was found where the user needs to select a city a second time to trigger data in to display
3. 2 classes were coded in 1 file - prefer to seperate these.




