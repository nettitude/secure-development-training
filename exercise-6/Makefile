all: build run
	echo Done

build:
	sudo docker build -t exercise-6 .

run: 
	sudo docker run -it --rm --name exercise-6 -p 80:80 -p 443:443 exercise-6
