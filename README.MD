# Transaction Processor

This project is a refactored and extended version of the provided PHP script for processing transaction commissions. It includes unit tests and is designed to be extendable for future enhancements.
See https://gist.github.com/naymkazp/87112812d3e273083979f3e36035e1e9

## Introduction

The purpose of this project is to calculate commissions for already made transactions. Transactions are provided in a JSON format, each in its own line in the input file. The BIN number represents the first digits of a credit card number, which can be used to determine the country where the card was issued. We apply different commission rates for EU-issued and non-EU-issued cards, and all commissions are calculated in EUR currency.

## Features

- Clean and modular codebase following OOP principles.
- Unit tests with mocking to ensure tests are not affected by dynamic data changes.
- Ceiling of commissions to the nearest cent.
- Extendable design to easily switch currency rates and BIN providers.

## Requirements

- Docker
- Docker Compose

## Setup

### Step 1: Clone the Repository

```bash
git clone https://github.com/vpshvd/bwt.git
cd bwt
```

### Step 2: Build and Start Docker Containers
To build and start the PHP and Caddy containers, run the following command:
```bash
docker compose up -d --build
```

### Step 3: Running the Code
```bash
docker exec -w /srv/app/src bwt-php php app.php input.txt
```

## Project Structure

- `src/`: Contains the application source code.
    - `ApiClient/`: Contains the API client implementation.
    - `DTO/`: Contains Data Transfer Objects.
    - `Enum/`: Contains enumerations.
    - `Exception/`: Contains custom exceptions.
    - `Service/`: Contains service classes.
- `tests/`: Contains unit tests.

## Usage

Place your input transactions in the `input.txt` file in the root directory. Each transaction should be in JSON format:

```json
{"bin":"45717360","amount":"100.00","currency":"EUR"}
{"bin":"516793","amount":"50.00","currency":"USD"}
{"bin":"45417360","amount":"10000.00","currency":"JPY"}
{"bin":"41417360","amount":"130.00","currency":"USD"}
{"bin":"4745030","amount":"2000.00","currency":"GBP"}
```

Run the script using the Docker command provided above. The output will be the calculated commissions for each transaction.
