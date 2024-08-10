docker-create-network:
	docker network create symfony-network

docker-create-postgres-volume:
	docker volume create --name postgres

docker-run-postgres:
	docker run -d --name postgres \
		--env POSTGRES_PASSWORD: Pwdpostgres022023!* \
        --env POSTGRES_USER: breizh81 \
        --env POSTGRES_DB: market-price-list \
        --network symfony-network \
        --volume postgres:12
