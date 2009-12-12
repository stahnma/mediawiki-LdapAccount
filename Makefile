NAME=mediawiki-LdapAccount
SPEC_FILE=SPECS/$(NAME).spec

RPMBUILD := $(shell if test -f /usr/bin/rpmbuild ; then echo /usr/bin/rpmbuild ; else echo "x" ; fi)

RPM_DEFINES =   --define "_specdir $(shell pwd)/SPECS" --define "_rpmdir $(shell pwd)/RPMS" --define "_sourcedir $(shell pwd)/SOURCES" --define  "_srcrpmdir $(shell pwd)/SRPMS" --define "_builddir $(shell pwd)/BUILD"

MAKE_DIRS= $(shell pwd)/SPECS $(shell pwd)/SOURCES $(shell pwd)/BUILD $(shell pwd)/SRPMS $(shell pwd)/RPMS

.PHONEY: listing listing spec uninstall

rpmcheck:
ifeq ($(RPMBUILD),x)
	$(error "rpmbuild not found, exiting...")
endif
	@mkdir -p $(MAKE_DIRS)

tarball:
	cd .. ; tar -p -c -v -z --exclude ".svn" --exclude ".git" --exclude $(NAME).tar.gz -f  /tmp/$(NAME).tar.gz $(NAME)
	@mv -f /tmp/$(NAME).tar.gz .

## use this to build an srpm locally
srpm:  rpmcheck
	@wait
	$(RPMBUILD) $(RPM_DEFINES)  -bs $(SPEC_FILE)
	@mv -f SRPMS/* .
	@rm -rf BUILD SRPMS RPMS

## use this to build rpm locally
rpm:   rpmcheck 
	@wait
	$(RPMBUILD) $(RPM_DEFINES) -bb  $(SPEC_FILE)
	@mv -f RPMS/noarch/* .

clean:
	rm -rf BUILD SRPMS RPMS *.rpm *tar.gz
