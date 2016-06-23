##Installation
```shell
git clone 
cd <project>
composer.phar install
```

Now you need to setup some configuration files. I tried to make it convinient so that you can generate sample structure of required files and folder. where you can replace your value. Like Redmine api key, You may have different ids for other attributes like tracker_id, project_id and so on. Put follow command in your terminal. I'm assuming you are still in your project root folder.

```shell
./bin/OnBoard setup
```
It will create one file located at config/main.json and it will create two more csv files where you will put your stories to be imported. assets/common.csv and assets/backend-engineer.csv

Let's add yoru configurations first.

Now open csv files and put your stories sa per the heading of each colomn. Once completed
