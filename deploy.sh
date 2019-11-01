#! /bin/bash
# See https://github.com/GaryJones/wordpress-plugin-svn-deploy for instructions and credits.
#
# Steps to deploying:
#
#  1. Ask for plugin slug.
#  2. Ask for local plugin directory.
#  3. Check local plugin directory exists.
#  4. Ask for main plugin file name.
#  5. Check main plugin file exists.
#  6. Check readme.txt version matches main plugin file version.
#  7. Ask for temporary SVN path.
#  8. Ask for remote SVN repo.
#  9. Ask for SVN username.
# 10. Ask if input is correct, and give chance to abort.
# 11. Check if Git tag exists for version number (must match exactly).
# 12. Checkout SVN repo.
# 13. Set to SVN ignore some GitHub-related files.
# 14. Export HEAD of master from git to the trunk of SVN.
# 15. Initialise and update and git submodules.
# 16. Move /trunk/assets up to /assets.
# 17. Move into /trunk, and SVN commit.
# 18. Move into /assets, and SVN commit.
# 19. Copy /trunk into /tags/{version}, and SVN commit.
# 20. Delete temporary local SVN checkout.

echo
echo "========== WordPress Plugin SVN Deploy v3.1.0 =========="
echo

# Get some user input
# Can't use the -i flag for read, since that doesn't work for bash 3
PLUGINSLUG="leira-roles"
#printf "1. WordPress Repo Plugin Slug is: $PLUGINSLUG"
#read -e PLUGINSLUG
#echo "$PLUGINSLUG"
#echo

# Set up some default values. Feel free to change these in your own script
CURRENTDIR=$(pwd)
default_svnpath="/tmp/$PLUGINSLUG"
default_svnurl="https://plugins.svn.wordpress.org/$PLUGINSLUG"
default_svnuser="arielhr1987"
default_plugindir="$CURRENTDIR"
default_mainfile="$PLUGINSLUG.php"

#echo "2. Your local plugin root directory (the Git repo)."
#printf "($default_plugindir): "
#read -e  input
input="${input%/}" # Strip trailing slash
PLUGINDIR="${input:-$default_plugindir}" # Populate with default if empty
#echo

# Check directory exists.
if [ ! -d "$PLUGINDIR" ]; then
  echo "Directory $PLUGINDIR not found. Aborting."
  exit 1;
fi

#printf "3. Name of the main plugin file ($default_mainfile): "
#read -e input
MAINFILE="${input:-$default_mainfile}" # Populate with default if empty
#echo

# Check main plugin file exists.
if [ ! -f "$PLUGINDIR/$MAINFILE" ]; then
  echo "Plugin file $PLUGINDIR/$MAINFILE not found. Aborting."
  exit 1;
fi

#echo "Checking version in main plugin file matches version in readme.txt file..."
#echo

# Check version in readme.txt is the same as plugin file after translating both to Unix line breaks to work around grep's failure to identify Mac line breaks
PLUGINVERSION=$(grep -i "Version:" $PLUGINDIR/$MAINFILE | awk -F' ' '{print $NF}' | tr -d '\r')
#echo "$MAINFILE version: $PLUGINVERSION"
READMEVERSION=$(grep -i "Stable tag:" $PLUGINDIR/readme.txt | awk -F' ' '{print $NF}' | tr -d '\r')
#echo "readme.txt version: $READMEVERSION"

if [ "$READMEVERSION" = "trunk" ]; then
	echo "Version in readme.txt & $MAINFILE don't match, but Stable tag is trunk. Let's continue..."
elif [ "$PLUGINVERSION" != "$READMEVERSION" ]; then
	echo "Version in readme.txt & $MAINFILE don't match. Exiting...."
	exit 1;
elif [ "$PLUGINVERSION" = "$READMEVERSION" ]; then
	#echo "Versions match in readme.txt and $MAINFILE. Let's continue..."
	echo "Plugin version: $PLUGINVERSION"
fi

#echo

#echo "4. Path to a local directory where a temporary SVN checkout can be made."
#printf "Don't add trunk ($default_svnpath): "
#read -e input
input="${input%/}" # Strip trailing slash
SVNPATH="${input:-$default_svnpath}" # Populate with default if empty
#echo

#echo "5. Remote SVN repo on WordPress.org."
#printf "($default_svnurl): "
#read -e input
input="${input%/}" # Strip trailing slash
SVNURL="${input:-$default_svnurl}" # Populate with default if empty
#echo

#printf "Q6. Your WordPress repo SVN username ($default_svnuser): "
#read -e input
SVNUSER="${input:-$default_svnuser}" # Populate with default if empty
#echo

#echo "That's all of the data collected."
#echo
echo "Plugin slug: $PLUGINSLUG"
echo "Plugin directory (The git repo): $PLUGINDIR"
echo "Main plugin file: $MAINFILE"
echo "Temp checkout path: $SVNPATH"
echo "Remote SVN repo: $SVNURL"
echo "SVN username: $SVNUSER"
echo

printf "OK to proceed (Y|n)? "
read -e input
PROCEED="${input:-y}"
echo

# Allow user cancellation
if [ $(echo "$PROCEED" |tr [:upper:] [:lower:]) != "y" ]; then echo "Aborting..."; exit 1; fi

# Let's begin...
echo ".........................................."
echo
echo "Preparing to deploy WordPress plugin"
echo
echo ".........................................."
echo

echo

echo "Changing to $PLUGINDIR"
cd $PLUGINDIR

# Check for git tag (may need to allow for leading "v"?)
# if git show-ref --tags --quiet --verify -- "refs/tags/$PLUGINVERSION"
if git show-ref --tags --quiet --verify -- "refs/tags/$PLUGINVERSION"
	then
		echo "Git tag $PLUGINVERSION does exist. Let's continue..."
	else
		echo "$PLUGINVERSION does not exist as a git tag. Aborting.";
		exit 1;
fi

echo

echo "Creating local copy of SVN repo trunk..."
svn checkout $SVNURL $SVNPATH --depth immediates
svn update --quiet $SVNPATH/trunk --set-depth infinity

echo "Ignoring GitHub specific files"
# Use local .svnignore if present
if [ -f ".svnignore" ]; then
	echo "Using local .svnignore"
	SVNIGNORE=$( <.svnignore )
else
	echo "Using default .svnignore"
	SVNIGNORE="README.md
Thumbs.db
deploy.sh
.github
.git
.gitattributes
.gitignore
composer.lock"
fi

svn propset svn:ignore \""$SVNIGNORE"\" "$SVNPATH/trunk/"

echo "Exporting the HEAD of master from git to the trunk of SVN"
git checkout-index -a -f --prefix=$SVNPATH/trunk/

# If submodule exist, recursively check out their indexes
if [ -f ".gitmodules" ]
	then
		echo "Exporting the HEAD of each submodule from git to the trunk of SVN"
		git submodule init
		git submodule update
		git config -f .gitmodules --get-regexp '^submodule\..*\.path$' |
			while read path_key path
			do
				#url_key=$(echo $path_key | sed 's/\.path/.url/')
				#url=$(git config -f .gitmodules --get "$url_key")
				#git submodule add $url $path
				echo "This is the submodule path: $path"
				echo "The following line is the command to checkout the submodule."
				echo "git submodule foreach --recursive 'git checkout-index -a -f --prefix=$SVNPATH/trunk/$path/'"
				git submodule foreach --recursive 'git checkout-index -a -f --prefix=$SVNPATH/trunk/$path/'
			done
fi

echo

# Support for the /assets folder on the .org repo.
echo "Moving assets."
# Make the directory if it doesn't already exist
mkdir -p $SVNPATH/assets/
mv $SVNPATH/trunk/assets/* $SVNPATH/assets/
svn add --force $SVNPATH/assets/
svn delete --force $SVNPATH/trunk/assets

echo

echo "Changing directory to SVN and committing to trunk."
cd $SVNPATH/trunk/
# Delete all files that should not now be added.
# Use $SVNIGNORE for `rm -rf`. Setting propset svn:ignore seems flaky.
echo "$SVNIGNORE" | awk '{print $0}' | xargs rm -rf
svn status | grep -v "^.[ \t]*\..*" | grep "^\!" | awk '{print $2"@"}' | xargs svn del
# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2"@"}' | xargs svn add
svn commit --username=$SVNUSER -m "Preparing for $PLUGINVERSION release"

echo

echo "Updating WordPress plugin repo assets and committing."
cd $SVNPATH/assets/
# Delete all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^\!" | awk '{print $2"@"}' | xargs svn del
# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2"@"}' | xargs svn add
svn update --quiet --accept working $SVNPATH/assets/*
svn resolve --accept working $SVNPATH/assets/*
svn commit --username=$SVNUSER -m "Updating assets"

echo

echo "Creating new SVN tag and committing it."
cd $SVNPATH
svn copy --quiet trunk/ tags/$PLUGINVERSION/
# Remove assets and trunk directories from tag directory
svn delete --force --quiet $SVNPATH/tags/$PLUGINVERSION/assets
svn delete --force --quiet $SVNPATH/tags/$PLUGINVERSION/trunk
svn update --quiet --accept working $SVNPATH/tags/$PLUGINVERSION
#svn resolve --accept working $SVNPATH/tags/$PLUGINVERSION/*
cd $SVNPATH/tags/$PLUGINVERSION
svn commit --username=$SVNUSER -m "Tagging version $PLUGINVERSION"

echo

echo "Removing temporary directory $SVNPATH."
cd $SVNPATH
cd ..
rm -fr $SVNPATH/

echo "*** FIN ***"