#!/bin/bash

NAME="mipanel"

VERSION=$(grep '^Version:' $NAME.spec | sed 's/[^0-9]*\([0-9\.]*\)[^0-9]*$/\1/')
RPMDIR=$(rpm --eval %{_topdir})

cp -f $NAME.spec $RPMDIR/SPECS
git archive --format=tar --prefix $NAME/ HEAD | gzip > $RPMDIR/SOURCES/$NAME-$VERSION.tar.gz
rpmbuild -ba $RPMDIR/SPECS/$NAME.spec $*
