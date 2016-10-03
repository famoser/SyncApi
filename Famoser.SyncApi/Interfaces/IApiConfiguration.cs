using System;
using System.Collections.Generic;
using System.Diagnostics.Contracts;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities;

namespace Famoser.SyncApi.Interfaces
{
    public interface IApiConfiguration
    {
        Uri GetApiUri();

        Task<Guid> GetUserIdAsync();
        /// <summary>
        /// "read access"
        /// </summary>
        /// <param name="groupIdentifier"></param>
        /// <returns></returns>
        Task<List<Guid>> GetGroupIdAsync(string groupIdentifier);
        /// <summary>
        /// "write access"
        /// </summary>
        /// <param name="groupIdentifier"></param>
        /// <returns></returns>
        Task<Guid> GetPrimaryGroupIdAsync(string groupIdentifier);
        Task<bool> AddGroupIdAsync(string groupIdentifier, Guid id);
    }
}
