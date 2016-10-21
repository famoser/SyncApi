using System;

namespace Famoser.SyncApi.Api.Configuration
{
    /// <summary>
    /// The information about your API
    /// </summary>
    public class ApiInformationEntity
    {
        /// <summary>
        /// A seed unique to your api. Can be any number, do not make it too large so no integer overflow is happening
        /// </summary>
        public int Seed { get; set; }
        /// <summary>
        /// this modulo together with the seed will be used to generate authentication tokens. Use a (large) prime number, you can generate one here: http://www.numberempire.com/primenumbers.php
        /// </summary>
        public int Modulo { get; set; }
        /// <summary>
        /// the URI of your API
        /// </summary>
        public Uri Uri { get; set; }
    }
}
