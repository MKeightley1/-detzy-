set /P "comment=Comment: "
git add .
git commit -m "%comment%"
git push        
PAUSE