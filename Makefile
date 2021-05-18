all: build_all clean
build_all:
	mkdir build
	php build.php

clean:
	rm -rf build
