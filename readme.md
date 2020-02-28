## TASK:

Financial Institution

A client is a financial institution that has an existing database that contains information about customer details. They want to be able to take the record of transactions which will, in turn, affect their customer balance.

## KEY REQUIREMENTS:

The User should be able to;

1. An endpoint that allows the institution to perform transactions on the customer’s account. i.e Debit, Credit, Freezing.
2. A transaction cannot be carried out on account with the status of inactive, dormant.
3. You should take note of the customer account number as it must conform to the NUBAN bank standard of 10 digits.
4. The chosen currency for the customer should be enforced for transactions.

## TOOLS TO USE:

1. Database: MySQL
2. API Framework: Laravel or SpringBoot.
3. Codebase should be pushed to a GitHub or BitBucket repository for code review.

## THINGS WE LOOK OUT FOR:

1. Simple Business logic
2. Clear separation of concerns between application logic and data logic
3. Tests.

## How to run the start the application

run ./run-migration.sh
this will run migration and preload the database with data.
