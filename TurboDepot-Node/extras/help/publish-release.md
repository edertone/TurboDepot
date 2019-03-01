# How to make the library available to the public:

1 - Commit and push all the new version changes to repository.

2 - Review git changelog to decide the new version value based on the GIT changes: minor, major, ...

3 - Make sure the git tag is updated with the new project version we want to publish
    (Either in git local and remote repos)
    
4 - Update the version number on the project root package.json file
    Make sure we have increased the version number regarding the previously published one

5 - Generate a release build executing tests (tb -crt)

6 - Copy the package.json file from the project root to target/turbodepot-node-x.x.x/dist/ts

7 - Add the readme.md file if exists to the target/turbodepot-node-x.x.x/dist/ts folder

8 - Open a command line inside target/turbodepot-node-x.x.x/dist/ts folder and run:
    npm publish
   
9 - Verify that new version appears for the package at www.npmjs.com/~edertone 
   
10 - Get the downloadable zip file and update the files inside with the new versions
   - docs, readme, compiled code, etc..  

11 - Upload the new zip version to turbodepot website for direct download
    - review that zip download works as expected

12 - Upload the new generated docs to the turbodepot website
    - review that links to docs still work
