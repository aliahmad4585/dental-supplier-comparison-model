# dental-supplier-comparison-model

Develop a model for a dental price comparison.
Find the best supplier for a customer and calculate the best price. The customer will input
the Product Type and the requested amount.


# Requirements
1. php ">=8.0.2",
2. symfony 6.0
3. symfony cli

# How to run the app

1. Clone the repository https://github.com/aliahmad4585/dental-supplier-comparison-model.git
2. Run composer install to install the dependencies
3. Run symfony server:start to start the server

# Postman collection 
1. find the api.postman_collection.json in repostory
2. import the json file into postmai
3. replace the base url

api url http://localhost:8000/api/comparison

# sample payload
{
    "params":[
        {
            "type":"Dental Floss",
            "unit":5
        },
        {
            "type":"Ibuprofen",
            "unit":12
        }
    ]
}
# output format
{
    "result": {
        "Supplier": "supplier B",
        "price": 102
    }
}

