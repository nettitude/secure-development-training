all: build run
	echo Done

build:
	sudo docker build -t exercise-10 .

run: 
	sudo docker run -it --rm --name exercise-10 -p 80:80 -p 443:443 exercise-10
