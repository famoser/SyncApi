using System;

namespace Famoser.SyncApi.Api.Configuration
{
    /// <summary>
    /// The information about your API
    /// </summary>
    public class ApiInformationEntity
    {
        /// <summary>
        /// the URI of your API
        /// </summary>
        public Uri Uri { get; set; }
        /// <summary>
        /// A modulo unique to the api, you can generate one here: http://www.numberempire.com/primenumbers.php
        /// Why: Is used to authenticate your request, also so the genrated number is not that large. 
        /// </summary>
        public int Modulo { get; set; }
        /// <summary>
        /// A unique id for your application
        /// Why: The api will able to tell the difference between multiple application consuming it
        /// </summary>
        public string ApplicationId { get; set; }
        /// <summary>
        /// A seed unique to your application. Can be any number, do not make it too large so no integer overflow can happen
        /// Why: Is used to authenticate your request. As only the creator of the application knows this number, no other application can use its data
        /// </summary>
        public int ApplicationSeed { get; set; }
    }
}
